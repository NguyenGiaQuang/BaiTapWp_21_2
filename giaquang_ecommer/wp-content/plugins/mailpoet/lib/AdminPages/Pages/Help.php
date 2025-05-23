<?php declare(strict_types = 1);

namespace MailPoet\AdminPages\Pages;

if (!defined('ABSPATH')) exit;


use MailPoet\AdminPages\PageRenderer;
use MailPoet\Cron\ActionScheduler\Actions\DaemonRun;
use MailPoet\Cron\ActionScheduler\Actions\DaemonTrigger;
use MailPoet\Cron\CronHelper;
use MailPoet\Cron\Workers\SendingQueue\SendingQueue;
use MailPoet\Entities\ScheduledTaskEntity;
use MailPoet\Mailer\MailerLog;
use MailPoet\Newsletter\Sending\ScheduledTasksRepository;
use MailPoet\Newsletter\Sending\SendingQueuesRepository;
use MailPoet\Newsletter\Url as NewsletterURL;
use MailPoet\Router\Endpoints\CronDaemon;
use MailPoet\Services\Bridge;
use MailPoet\SystemReport\SystemReportCollector;
use MailPoet\WP\DateTime;
use MailPoet\WP\Functions as WPFunctions;

class Help {
  private PageRenderer $pageRenderer;
  private CronHelper $cronHelper;
  private SystemReportCollector $systemReportCollector;
  private Bridge $bridge;
  private ScheduledTasksRepository $scheduledTasksRepository;
  private SendingQueuesRepository $sendingQueuesRepository;
  private NewsletterURL $newsletterUrl;

  public function __construct(
    PageRenderer $pageRenderer,
    CronHelper $cronHelper,
    SystemReportCollector $systemReportCollector,
    Bridge $bridge,
    ScheduledTasksRepository $scheduledTasksRepository,
    SendingQueuesRepository $sendingQueuesRepository,
    NewsletterURL $newsletterUrl
  ) {
    $this->pageRenderer = $pageRenderer;
    $this->cronHelper = $cronHelper;
    $this->systemReportCollector = $systemReportCollector;
    $this->bridge = $bridge;
    $this->scheduledTasksRepository = $scheduledTasksRepository;
    $this->sendingQueuesRepository = $sendingQueuesRepository;
    $this->newsletterUrl = $newsletterUrl;
  }

  public function render() {
    /**
     * Filter the system info.
     *
     * @param array<string, string> $systemInfoData The system info data array.
     */
    $systemInfoData = WPFunctions::get()->applyFilters('mailpoet_system_info_data', $this->systemReportCollector->getData(true));

    try {
      $cronPingUrl = $this->cronHelper->getCronUrl(CronDaemon::ACTION_PING);
      $cronPingResponse = $this->systemReportCollector->getCronPingResponse();
    } catch (\Exception $e) {
      $cronPingResponse = __('Can‘t generate cron URL.', 'mailpoet') . ' (' . $e->getMessage() . ')';
      $cronPingUrl = $cronPingResponse;
    }

    $mailerLog = MailerLog::getMailerLog();
    $mailerLog['sent'] = MailerLog::sentSince();
    $bridgePingResponse = $this->systemReportCollector->getBridgePingResponse();
    $systemStatusData = [
      'cron' => [
        'url' => $cronPingUrl,
        'isReachable' => $this->cronHelper->validatePingResponse($cronPingResponse),
        'pingResponse' => $cronPingResponse,
      ],
      'mss' => [
        'enabled' => $this->bridge->isMailpoetSendingServiceEnabled(),
        'isReachable' => $this->bridge->validateBridgePingResponse($bridgePingResponse),
      ],
      'cronStatus' => $this->cronHelper->getDaemon(),
      'queueStatus' => $mailerLog,
    ];

    $systemStatusData['cronStatus']['accessible'] = $this->cronHelper->isDaemonAccessible();
    $systemStatusData['queueStatus']['tasksStatusCounts'] = $this->scheduledTasksRepository->getCountsPerStatus();

    $scheduledTasks = $this->scheduledTasksRepository->getLatestTasks(SendingQueue::TASK_TYPE);
    $systemStatusData['queueStatus']['latestTasks'] = array_map(fn($task) => $this->buildTaskData($task), $scheduledTasks);

    $this->pageRenderer->displayPage(
      'help.html',
      [
        'systemInfoData' => $systemInfoData,
        'systemStatusData' => $systemStatusData,
        'actionSchedulerData' => $this->getActionSchedulerData(),
      ]
    );
  }

  private function getActionSchedulerData(): ?array {
    if (!class_exists(\ActionScheduler_Store::class)) {
      return null;
    }
    $actionSchedulerData = [];
    $actionSchedulerData['version'] = \ActionScheduler_Versions::instance()->latest_version();
    $actionSchedulerData['storage'] = str_replace('ActionScheduler_', '', get_class(\ActionScheduler_Store::instance()));
    $actionSchedulerData['latestTrigger'] = $this->getLatestActionSchedulerActionDate(DaemonTrigger::NAME);
    $actionSchedulerData['latestCompletedTrigger'] = $this->getLatestActionSchedulerActionDate(DaemonTrigger::NAME, 'complete');
    $actionSchedulerData['latestCompletedRun'] = $this->getLatestActionSchedulerActionDate(DaemonRun::NAME, 'complete');
    return $actionSchedulerData;
  }

  private function getLatestActionSchedulerActionDate(string $hook, ?string $status = null): ?string {
    $query = [
      'per_page' => 1,
      'order' => 'DESC',
      'hook' => $hook,
    ];
    if ($status) {
      $query['status'] = $status;
    }
    $store = \ActionScheduler_Store::instance();
    $action = $store->query_actions($query);
    if (!empty($action)) {
      $dateObject = $store->get_date($action[0]);
      return $dateObject->format('Y-m-d H:i:s');
    }
    return null;
  }

  public function buildTaskData(ScheduledTaskEntity $task): array {
    $queue = $newsletter = $subscriber = null;
    if ($task->getType() === SendingQueue::TASK_TYPE) {
      $queue = $this->sendingQueuesRepository->findOneBy(['task' => $task]);
      $newsletter = $queue ? $queue->getNewsletter() : null;
      $subscribers = $task->getSubscribers();
      // We only show subscriber's email for 1:1 emails (e.g. automations) and not bulk campaigns
      if ($subscribers->count() === 1) {
        $subscriber = $subscribers->first() ? $subscribers->first()->getSubscriber() : null;
      }
    }

    return [
      'id' => $task->getId(),
      'type' => $task->getType(),
      'priority' => $task->getPriority(),
      'updatedAt' => $task->getUpdatedAt()->format(DateTime::DEFAULT_DATE_TIME_FORMAT),
      'scheduledAt' => $task->getScheduledAt() ?
        $task->getScheduledAt()->format(DateTime::DEFAULT_DATE_TIME_FORMAT)
        : null,
      'cancelledAt' => $task->getCancelledAt() ?
        $task->getCancelledAt()->format(DateTime::DEFAULT_DATE_TIME_FORMAT)
        : null,
      'status' => $task->getStatus(),
      'newsletter' => $queue && $newsletter ? [
        'newsletterId' => $newsletter->getId(),
        'queueId' => $queue->getId(),
        'subject' => $queue->getNewsletterRenderedSubject() ?: $newsletter->getSubject(),
        'previewUrl' => $this->newsletterUrl->getViewInBrowserUrl($newsletter, null, $queue),
      ] : [
        'newsletterId' => null,
        'queueId' => null,
        'subject' => null,
        'previewUrl' => null,
      ],
      'subscriberEmail' => $subscriber ? $subscriber->getEmail() : null,
    ];
  }
}
