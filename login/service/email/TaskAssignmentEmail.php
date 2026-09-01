<?php
/**
 * Task Assignment Email template.
 *
 * Renders the professional HTML + plain-text versions of the task assignment
 * email (TASK_ASSIGNED / TENDER_TASK_ASSIGNED).
 *
 * Pure presentation: no database access, no mail transport, no external
 * assets. Every dynamic value is escaped before it is inserted into HTML so
 * task/tender data can never inject markup into the email.
 */

class TaskAssignmentEmail
{
    /** @var string Application name shown in the header/footer. */
    private $appName;

    public function __construct($appName = 'DVEPL')
    {
        $this->appName = $appName;
    }

    /** Dynamic subject for the assignment email. */
    public function subject(array $task, $type = 'TASK_ASSIGNED')
    {
        if ($type === 'TENDER_TASK_ASSIGNED' && !empty($task['tenderID'])) {
            return 'New Tender Task Assigned — ' . $task['tenderID'];
        }
        return 'New Task Assigned — ' . $task['title'];
    }

    /** Full HTML version (inline CSS, table-based, responsive, no external deps). */
    public function html(array $task, $type = 'TASK_ASSIGNED', $baseUrl = '')
    {
        $appName  = $this->h($this->appName);
        $subject  = $this->subject($task, $type);
        $employee = $this->h($task['assigned_username']);
        $taskUrl  = $baseUrl . '/login/task-management/view.php?id=' . (int) $task['id'];

        // Task details key/value rows (empty fields are skipped)
        $rows = '';
        $rows .= $this->kvRow('Task Title', $this->h($task['title']));
        $rows .= $this->kvRow('Task ID', '#' . (int) $task['id']);
        $rows .= $this->kvRow('Priority', $this->h($task['priority']));
        $rows .= $this->kvRow('Status', $this->h($task['status']));
        $rows .= $this->kvRow('Assigned By', $this->h($task['creator_username']));
        $rows .= $this->kvRow('Assigned To', $this->h($task['assigned_username']));
        $rows .= $this->kvRow('Start Date', $this->fmtDate($task['start_date']));
        $rows .= $this->kvRow('Due Date', $this->fmtDate($task['due_date']));
        $rows .= $this->kvRow('Created Date', $this->fmtDateTime($task['created_at']));

        // Tender/Query section + button (only when a related tender exists)
        $tenderSection = '';
        $tenderButton  = '';
        if ($this->hasTender($task)) {
            $tenderRows = '';
            $tenderRows .= $this->kvRow('Tender ID', $this->h($task['tenderID']));
            $tenderRows .= $this->kvRow('Reference Code', $this->h($task['reference_code']));
            $tenderRows .= $this->kvRow('Tender Status', $this->h($task['tender_status']));
            $tenderRows .= $this->kvRow('Tender Due Date', $this->h($task['tender_due_date']));
            $tenderRows .= $this->kvRow('Registered By', $this->h($this->registeredByRaw($task)));
            $tenderRows .= $this->kvRow('Tender Created Date', $this->fmtDateTime($task['tender_created_at']));

            $tenderSection = $this->sectionHeader('Tender / Query Details') . $this->kvTable($tenderRows);

            $tenderUrl = $baseUrl . '/login/sent-edit.php?id=' . base64_encode((string) $task['tender_request_id']);
            $tenderButton = '<td style="padding:0 0 0 6px;">'
                . '<a href="' . $this->attr($tenderUrl) . '" '
                . 'style="display:inline-block;background-color:#ffffff;color:#33cc33;text-decoration:none;padding:9px 21px;border-radius:4px;font-size:14px;font-weight:bold;border:1px solid #33cc33;font-family:Arial,Helvetica,sans-serif;">View Tender</a>'
                . '</td>';
        }

        // Task description section (only when a description exists)
        $descriptionSection = '';
        if (!empty($task['description'])) {
            $descriptionSection = $this->sectionHeader('Task Description')
                . '<p style="margin:0 0 22px 0;padding:12px 14px;background-color:#f8fafc;border:1px solid #eef1f5;border-radius:4px;color:#333333;">'
                . nl2br($this->h($task['description']))
                . '</p>';
        }

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$this->h($subject)}</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f6f8;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8;padding:24px 12px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;width:100%;background-color:#ffffff;border:1px solid #e3e8ee;border-radius:6px;">
          <tr>
            <td style="background-color:#33cc33;padding:24px 28px;">
              <div style="font-size:22px;font-weight:bold;color:#ffffff;font-family:Arial,Helvetica,sans-serif;">{$appName}</div>
              <div style="font-size:12px;color:#eafff4;margin-top:4px;text-transform:uppercase;letter-spacing:1px;font-family:Arial,Helvetica,sans-serif;">Task Assignment Notification</div>
            </td>
          </tr>
          <tr>
            <td style="padding:26px 28px 30px 28px;font-family:Arial,Helvetica,sans-serif;color:#222222;font-size:14px;line-height:1.6;">
              <p style="margin:0 0 6px 0;">Dear {$employee},</p>
              <p style="margin:0;">You have been assigned a new task.</p>

              {$this->sectionHeader('Task Details')}
              {$this->kvTable($rows)}

              {$tenderSection}

              {$descriptionSection}

              <table role="presentation" cellpadding="0" cellspacing="0" style="margin:4px 0 0 0;">
                <tr>
                  <td style="padding:0 6px 0 0;">
                    <a href="{$this->attr($taskUrl)}" style="display:inline-block;background-color:#33cc33;color:#ffffff;text-decoration:none;padding:10px 22px;border-radius:4px;font-size:14px;font-weight:bold;font-family:Arial,Helvetica,sans-serif;">View Task</a>
                  </td>
                  {$tenderButton}
                </tr>
              </table>

              <p style="margin:26px 0 0 0;padding-top:16px;border-top:1px solid #eef1f5;color:#8a97a5;font-size:12px;line-height:1.6;font-family:Arial,Helvetica,sans-serif;">
                This is an automated notification from {$appName}.<br>
                Please do not reply directly to this email.
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;

        return $html;
    }

    /** Plain-text version of the same email (always sent as the AltBody). */
    public function text(array $task, $type = 'TASK_ASSIGNED', $baseUrl = '')
    {
        $appName = $this->appName;
        $taskUrl = $baseUrl . '/login/task-management/view.php?id=' . (int) $task['id'];

        $lines = [];
        $lines[] = $appName . ' — Task Assignment Notification';
        $lines[] = '';
        $lines[] = 'Dear ' . $task['assigned_username'] . ',';
        $lines[] = '';
        $lines[] = 'You have been assigned a new task.';
        $lines[] = '';

        $lines[] = 'TASK DETAILS';
        $lines[] = 'Task Title: ' . $task['title'];
        $lines[] = 'Task ID: #' . (int) $task['id'];
        $lines[] = 'Priority: ' . $task['priority'];
        $lines[] = 'Status: ' . $task['status'];
        $lines[] = 'Assigned By: ' . $task['creator_username'];
        $lines[] = 'Assigned To: ' . $task['assigned_username'];
        if ($this->fmtDate($task['start_date']) !== '') {
            $lines[] = 'Start Date: ' . $this->fmtDate($task['start_date']);
        }
        if ($this->fmtDate($task['due_date']) !== '') {
            $lines[] = 'Due Date: ' . $this->fmtDate($task['due_date']);
        }
        if ($this->fmtDateTime($task['created_at']) !== '') {
            $lines[] = 'Created Date: ' . $this->fmtDateTime($task['created_at']);
        }
        $lines[] = '';

        if ($this->hasTender($task)) {
            $lines[] = 'TENDER / QUERY DETAILS';
            $lines[] = 'Tender ID: ' . $task['tenderID'];
            if (!empty($task['reference_code'])) {
                $lines[] = 'Reference Code: ' . $task['reference_code'];
            }
            if (!empty($task['tender_status'])) {
                $lines[] = 'Tender Status: ' . $task['tender_status'];
            }
            if (!empty($task['tender_due_date'])) {
                $lines[] = 'Tender Due Date: ' . $task['tender_due_date'];
            }
            if ($this->registeredByRaw($task) !== '') {
                $lines[] = 'Registered By: ' . $this->registeredByRaw($task);
            }
            if ($this->fmtDateTime($task['tender_created_at']) !== '') {
                $lines[] = 'Tender Created Date: ' . $this->fmtDateTime($task['tender_created_at']);
            }
            $lines[] = '';
        }

        if (!empty($task['description'])) {
            $lines[] = 'TASK DESCRIPTION';
            $lines[] = $task['description'];
            $lines[] = '';
        }

        $lines[] = 'View Task: ' . $taskUrl;
        if ($this->hasTender($task)) {
            $lines[] = 'View Tender: ' . $baseUrl . '/login/sent-edit.php?id=' . base64_encode((string) $task['tender_request_id']);
        }
        $lines[] = '';
        $lines[] = 'This is an automated notification from ' . $appName . '.';
        $lines[] = 'Please do not reply directly to this email.';

        return implode("\n", $lines);
    }

    /** Whether the task is linked to a usable Tender/Query record. */
    private function hasTender(array $task)
    {
        return !empty($task['tender_request_id']) && !empty($task['tenderID']);
    }

    /** Raw (unescaped) "registered by" value: member name (firm), else updated_by. */
    private function registeredByRaw(array $task)
    {
        $name = trim((string) ($task['member_name'] ?? ''));
        if ($name !== '') {
            $firm = trim((string) ($task['member_firm'] ?? ''));
            return $firm !== '' ? $name . ' (' . $firm . ')' : $name;
        }
        return trim((string) ($task['updated_by'] ?? ''));
    }

    /** Single key/value row; returns '' when the value is empty (no empty fields). */
    private function kvRow($label, $value)
    {
        if ($value === null || $value === '') {
            return '';
        }
        return '<tr>'
            . '<td style="padding:5px 0;color:#6b7a8d;width:150px;vertical-align:top;">' . $label . '</td>'
            . '<td style="padding:5px 0;color:#222222;font-weight:bold;">' . $value . '</td>'
            . '</tr>';
    }

    private function kvTable($rows)
    {
        return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 22px 0;font-size:14px;line-height:1.6;">'
            . $rows
            . '</table>';
    }

    private function sectionHeader($title)
    {
        return '<h2 style="font-size:12px;text-transform:uppercase;letter-spacing:1px;color:#33cc33;border-bottom:1px solid #eef1f5;padding-bottom:6px;margin:24px 0 10px 0;">'
            . $this->h($title)
            . '</h2>';
    }

    /** Format a Y-m-d value as d-m-Y; falls back to the raw value when unparseable. */
    private function fmtDate($value)
    {
        if ($value === null || $value === '') {
            return '';
        }
        $ts = strtotime((string) $value);
        return $ts ? date('d-m-Y', $ts) : $this->h($value);
    }

    /** Format a datetime value as d-m-Y H:i; falls back to the raw value when unparseable. */
    private function fmtDateTime($value)
    {
        if ($value === null || $value === '') {
            return '';
        }
        $ts = strtotime((string) $value);
        return $ts ? date('d-m-Y H:i', $ts) : $this->h($value);
    }

    private function h($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    private function attr($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
