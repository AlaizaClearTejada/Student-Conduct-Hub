# Automated Notifications Engine Guide

This document details the architecture, implementation, and workflows for the **Automated Notifications Engine** in the Student Conduct Management System (SCMS). This module is responsible for orchestrating multi-channel alerts (SMS, Email, In-App) to various stakeholders when case-related events occur.

---

## 1. Core Architecture

The Notifications Engine employs a **Service-Oriented Architecture** with decoupled components, ensuring high reliability, scalability, and delivery guarantees. 

### Processing Pipeline
1. **Event Trigger:** An application event (e.g., `ComplaintFiled`) is fired.
2. **Listener Invocation:** A listener (e.g., `SendComplaintNotifications`) intercepts the event and delegates it to the `NotificationService`.
3. **Recipient & Channel Mapping:** The `NotificationService` identifies the stakeholders and filters the allowed channels (SMS, Email, In-App) by consulting the user's `NotificationPreference` and the `RuleEngine`.
4. **Database Persistence:** The notification is saved to the `notifications` table in a `queued` state.
5. **Background Queue:** The `SendNotificationJob` is dispatched to the Redis queue.
6. **Channel Delivery:** Queue workers process the job, dynamically resolving the channel provider (`SemaphoreProvider`, `TwilioProvider`, etc.) and executing the API call.
7. **Delivery Logging:** Responses are logged in `notification_channel_logs`.

---

## 2. Dynamic Routing & Rules

Stakeholders have granular control over how and when they are notified.

### Rule Engine
The `RuleEngine` evaluates configurations set in `config/notifications.php` alongside user preferences. It enforces:
* **Opt-outs:** Users can unsubscribe from non-critical event types.
* **Quiet Hours:** Notifications triggered during designated quiet hours are deferred (except for `critical` priority).
* **Channel Toggles:** Users can globally disable SMS or Email.

### Stakeholder Context Mapping
The `RecipientMapper` dynamically resolves which specific `User` models correspond to theoretical roles (e.g., mapping `accused` to the student involved in a specific `TribunalCase`).

---

## 3. Database Schema

The system tracks all notification lifecycles for auditing and compliance.

### Tables
| Table Name | Purpose |
| :--- | :--- |
| **`notifications`** | Core table tracking the event type, case relation, subject, body, priority, and overall delivery status (`queued`, `sent`, `failed`). |
| **`notification_channel_logs`** | Detailed logs for each channel attempt, storing provider external IDs, error messages, and API responses. |
| **`notification_preferences`** | Stores user-level settings (channel toggles, daily limits, quiet hours, opt-outs). |
| **`notification_templates`** | Database-backed templates for customizable, brand-compliant messaging. |
| **`notification_rules`** | Dynamic routing configurations. |

---

## 4. Multi-Channel Integration

### SMS (Short Message Service)
* **Providers:** Semaphore, Twilio.
* **Integration:** Direct HTTP API integration via Laravel's `Http` facade.
* **Features:** Automatic concatenation, local phone number formatting, retry policies with exponential backoff.

### Email
* **Providers:** AWS SES, SendGrid, standard SMTP.
* **Integration:** Handled via Laravel's native Mail components and `EmailChannel` wrapper.
* **Features:** HTML5 rendering, DKIM signing, bounce tracking.

### In-App (Real-Time)
* **Providers:** Laravel Reverb (WebSockets).
* **Integration:** Handled via `InAppChannel` and Laravel Broadcasting.
* **Features:** Real-time push, unread badge counters, fallback polling.

---

## 5. Reliability & Queuing

To prevent blocking the main web application thread, all external API calls are strictly handled asynchronously.

* **Redis Queue:** Laravel Horizon/Redis manages the job queue.
* **Retry Strategy:** `SendNotificationJob` is configured with `tries = 3` and an exponential `backoff = [1, 5, 15]`.
* **Graceful Degradation:** If SMS fails but Email succeeds, the system correctly logs the partial failure while maintaining the overall `sent` state for successful channels.

---

## 6. Key Implementation Files

* **Main Service:** `app/Services/Notifications/NotificationService.php`
* **Queue Job:** `app/Jobs/SendNotificationJob.php`
* **Models:** `app/Models/Notification.php`, `app/Models/NotificationPreference.php`
* **Configuration:** `config/notifications.php`
* **API Endpoints:** `routes/api.php` (User preferences, Notification history, Webhooks)
* **Testing:** `tests/Feature/Notifications/NotificationServiceTest.php`
