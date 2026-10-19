# SMS Module - Developer Task Checklist (چک‌لیست توسعه ماژول پیامک)

Ordered by priority. Every status line is derived from the code and migrations on branch `sms-module`
at the time of writing; nothing here is a planned or invented feature. Statuses:
**DONE** = implemented and covered by code, **PARTIAL** = implemented with a stated gap,
**MISSING** = not found in the code.

---

## P1 - RBAC and privacy (دسترسی و حریم خصوصی)

- [ ] **1.1 Four access tiers (Silver / Gold / Diamond / Titanium) and the tier a query needs**
  Status: DONE.
  Files: `app/Services/Sms/SmsAccessTierService.php` (`getRequiredTier`, `effectiveTier`, `canAccess`,
  `assertCanQuery`), `app/Exceptions/Sms/TierAccessDeniedException.php`, `users.access_tier` column,
  admin screens in `resources/views/Admin/sms/settings/access_tiers`.
  Rules in code: cross-business or organization targeting = Titanium; advertising recipient pool and
  category/product/service filters = Diamond; user-database filters (role, birth, gender, province, city, dates) = Gold;
  own requests, custom numbers, Excel = Silver. superAdmin is always Titanium.

- [ ] **1.2 Hide business/agency filters from lower tiers, simplified filter subset**
  Status: DONE.
  Files: `SmsAccessTierService::requestFilterFieldTiers()` / `requestFilterFieldsFor()`,
  `resources/views/Admin/callcenter/partials/filter-modal.blade.php`,
  `resources/views/Admin/callcenter/partials/filter-fields.blade.php`,
  `resources/views/Admin/filter/productServiceFilterInputs.blade.php`.
  The modal renders only fields the user's tier may use (not rendered = not submitted) and shows the
  hint about more filters at higher tiers. Server enforcement is unchanged (1.1).
  Note: the request-filter rules in code have no Gold-only field, so Silver and Gold see the same subset;
  Diamond adds category/product/service fields; Titanium adds organization/representation/business.
  Test: `tests/Feature/SmsFilterTierSubsetTest.php`.

- [ ] **1.3 OTP is internal-only (Titanium / superAdmin)**
  Status: DONE.
  Files: `SmsAccessTierService::canUseOtp()`, `app/Http/Middleware/EnsureSmsOtpAccess.php`,
  `routes/admin_sms_financial.php` (OTP credit and OTP reports), `SendSingleSmsRequest::authorize()`.

- [ ] **1.4 Phone-number visibility permission**
  Status: DONE.
  Files: `SmsAccessTierService::canViewPhoneNumbers()` (`view-phone-numbers`), used for bulk preview
  samples and the requests-page "send SMS" button.

- [ ] **1.5 Confidential message redaction (مالی / حقوقی)**
  Status: DONE.
  Files: `app/Services/Sms/SmsConfidentialityGuard.php`, `SmsMessageGroup::isConfidential()`,
  migration `2026_10_11_000001_add_view_confidential_sms_permission.php`,
  `app/Http/Resources/SmsMessageResource.php`, `SmsLogController`.
  Classification is applied only to OTP; promotional sends are always `regular`.

- [ ] **1.6 Regulatory contract gate before any send**
  Status: DONE.
  Files: `app/Http/Middleware/EnsureSmsContractSigned.php` on all send/report route groups
  (`routes/admin_sms_*.php`), `SmsContractOtpService`, migrations
  `2026_10_11_000002_add_sms_contract_acceptance_to_ghorfe_online_lists_table.php` and
  `2026_10_16_000002_create_sms_contract_otps_table.php`.

- [ ] **1.7 Multi-tenant isolation of templates, groups, sender numbers**
  Status: DONE.
  Files: `ghorfe_online_list_id` on templates/groups (migration
  `2026_10_12_000002_separate_categories_and_templates_per_business.php`), checks in
  `SmsMessageTemplateController`, `SendSingleSmsRequest::withValidator()`, `BulkSmsSendService::resolveAdvertisingSenderNumber()`.

- [ ] **1.8 Enforcement of permitted sending hours**
  Status: MISSING. The contract text mentions allowed hours, but no time-window check exists in
  `app/Services` or `config` (searched for hour/quiet-time logic). Only manual scheduling exists (item 2.4).

---

## P2 - Bulk workflow (ارسال انبوه)

- [ ] **2.1 Recipient filtering and preview**
  Status: DONE.
  Files: `app/Services/Sms/BulkSmsRecipientFilterService.php`, `PreviewBulkSmsRequest`,
  `SmsBulkSendController`, `resources/views/Admin/sms/bulk/index.blade.php`,
  `public/assets/dashboard/js/requestFilterPicker.js`, `requestFilterSms.js`.
  Test: `tests/Feature/SmsAudienceFiltersTest.php`, `SmsRequestAudienceTest.php`.

- [ ] **2.2 Cost calculation, balance guard, max-recipients cap**
  Status: DONE.
  Files: `BulkSmsSendService` (`calculateMessageSegmentation`, `calculateCampaignCost`, `verifyBalance`,
  `maxAffordableRecipients`), `app/Services/Sms/BalanceGuard.php`,
  `app/Exceptions/Sms/InsufficientBalanceException.php`.

- [ ] **2.3 Campaign, recipients and transactions persistence**
  Status: DONE.
  Files: migrations `2026_10_07_000009_create_sms_campaigns_table.php`,
  `..._000010_create_sms_messages_table.php`, `..._000011_create_sms_transactions_table.php`,
  `..._000012_create_sms_campaign_recipients_table.php`; models `SmsCampaign`, `SmsCampaignRecipient`,
  `SmsMessage`, `SmsTransaction`.

- [ ] **2.4 Scheduling and due-campaign dispatch**
  Status: DONE.
  Files: `BulkSmsSendService::dispatchDueCampaigns()`, `executeCampaign()`, `app/Console/Kernel.php`.

- [ ] **2.5 Outgoing queue (save unsent compose, resume later)**
  Status: DONE.
  Files: `BulkSmsSendService::saveToQueue()`, `queuedCampaignToInput()`, `sendQueuedCampaign()`,
  `SmsCampaign::STATUS_PENDING_QUEUE`, migration `2026_10_19_000002_add_pending_queue_status_to_sms_campaigns.php`,
  `SmsBulkSendController::saveToQueue()`. Test: `tests/Feature/SmsBulkQueueTest.php`.

- [ ] **2.6 AI campaign assistant**
  Status: DONE.
  Files: `app/Services/Sms/AiCampaignAssistant.php`, `SmsAiCampaignController`, `routes/admin_sms_ai.php`.
  Test: `tests/Feature/SmsAiCampaignTest.php`.

- [ ] **2.7 Opt-out phrase on advertising messages**
  Status: DONE.
  Files: `SingleSmsSendService::CANCEL_PHRASE`, `BulkSmsSendService`, inbound handling in
  `app/Console/Commands/Spreadsms.php`, `app/Services/Sms/SmsReservedCodes.php`.

- [ ] **2.8 Top-up / payment of advertising credit**
  Status: DONE (gateway is wired through an interface).
  Files: `SmsTopUpPaymentService`, `SmsTopUpCallbackHandler`, `SmsPaymentGateway`,
  `PaymentModuleSmsGateway`, `UnconfiguredSmsPaymentGateway` (used when no gateway is configured),
  migration `2026_10_21_000001_create_sms_top_up_payments_table.php`.

---

## P3 - OTP vs promotional decoupling (تفکیک OTP و تبلیغاتی)

- [ ] **3.1 Separate balances**
  Status: DONE.
  Files: `app/Models/OtpBalance.php`, `app/Models/AdvertisingBalance.php`, migrations `..._000006_create_otp_balances_table.php`,
  `..._000007_create_advertising_balances_table.php`, `2026_10_15_000001_make_advertising_balance_per_business.php`.
  Test: `tests/Feature/SmsAdvertisingBalanceMigrationTest.php`.

- [ ] **3.2 Separate sender numbers and panels**
  Status: DONE.
  Files: `SenderNumber`, `BusinessSenderNumber`, `SmsPanel`, migrations `..._000001_create_sender_numbers_table.php`,
  `..._000004_create_sms_panels_table.php`, `..._000005_create_business_sender_number_table.php`.
  OTP sends never accept a sender override (`SingleSmsSendService`).

- [ ] **3.3 Fixed OTP categories, independent of subject groups**
  Status: DONE.
  Files: `app/Enums/SmsOtpCategory.php` (auth_password_reset, sales, finance, legal), migration
  `2026_10_20_000001_add_otp_category_to_sms_templates_and_messages.php`, `SendSingleSmsRequest::withValidator()`.
  Test: `tests/Feature/SmsOtpCategoryTest.php`.

- [ ] **3.4 Promotional subject-category restriction**
  Status: DONE.
  Allowed: event/greeting (`monasebati`), sales (`foroush`), support (`poshtibani`) and their subgroups.
  Single definition: `SmsMessageGroup::PROMOTIONAL_ROOT_SLUGS`, `scopePromotional()`, `scopePromotionalRoots()`,
  `isPromotional()`, `isPromotionalId()` in `app/Models/SmsMessageGroup.php`.
  Enforced in `SendBulkSmsRequest`, `SendSingleSmsRequest` (non-OTP only), `SmsBulkSendController::saveToQueue()`,
  `GroupsmsController` (store paths), `SmsMessageTemplateController` (store/update for non-OTP templates).
  UI: bulk and group-SMS forms list only promotional groups; single form and template form hide the other
  groups unless OTP is selected. Existing rows that reference other groups still load (nothing filters stored data).
  Test: `tests/Feature/SmsPromotionalGroupsTest.php`.
  Gap: the legacy page `resources/views/Admin/groupsms/single.blade.php` (has an OTP toggle) still lists all groups.

- [ ] **3.5 Promotional flow rejects OTP templates**
  Status: DONE. Files: `SendBulkSmsRequest::withValidator()`, `BulkSmsSendService::assertPromotionalTemplate()`.

---

## P4 - Audit and ID resolution (ممیزی و تبدیل شناسه‌ها)

- [ ] **4.1 Sender audit trail for every outgoing SMS**
  Status: DONE.
  Files: `app/Services/Sms/SmsAuditService.php` (single, bulk, otp channels), `app/Models/SmsAuditLog.php`,
  migration `2026_10_18_000001_create_sms_audit_logs_and_sender_role.php` (sender_id, sender_role, access_tier,
  request_id, callcenter_id, channel, service_type, sent_at; `sms_messages.sender_role`).
  Called from `SingleSmsSendService` and `BulkSmsSendService`. Test: `tests/Feature/SmsAuditServiceTest.php`.
  Gap: inbound messages and auto-responder replies are not audited by `SmsAuditService` apart from the reply
  going through `SingleSmsSendService` (which does audit it).

- [ ] **4.2 Template placeholder resolution**
  Status: DONE. Files: `app/Services/Sms/DynamicParameterResolver.php` (user name, date, time, invoice id,
  payment link, request id), `SmsSenderInfoResolver.php` ({company_name}, {phone}, {address} by sender role).

- [ ] **4.3 Catalog ID to readable breadcrumb (Category > Sub > Service) in logs and reports**
  Status: DONE. Files: `app/Services/Sms/CatalogBreadcrumbResolver.php`, `SmsLogService`, `SmsReportService`.
  Missing ids degrade to `#id`. Tests: `SmsCatalogBreadcrumbResolverTest.php`, `SmsReportBreadcrumbTest.php`.

- [ ] **4.4 Link messages to the originating request/call-center row**
  Status: DONE. Files: migration `2026_10_16_000001_add_callcenter_id_to_sms_messages_and_recipients.php`.

- [ ] **4.5 Logs, reports, exports**
  Status: DONE. Files: `SmsLogService`, `SmsReportService`, `app/Exports/Sms*ReportExport.php`,
  `SmsLogController`, `SmsReportController`. Tests: `SmsLogAndWebhookTest.php`, `SmsFinancialAndReportTest.php`.

- [ ] **4.6 Per-template change history**
  Status: DONE. Files: `SmsMessageTemplateHistory`, migration `..._000008_create_sms_message_template_histories_table.php`.

---

## P5 - Auto-responder (پاسخگوی خودکار)

- [ ] **5.1 Rule storage (keyword, match type, reply text, optional sender line)**
  Status: DONE. Files: migration `2026_10_22_000001_create_sms_auto_responders_table.php`
  (`match_type` exact | starts_with, `normalized_keyword`, soft deletes, `sms_messages.auto_responder_id`),
  `app/Models/SmsAutoResponder.php`.

- [ ] **5.2 Rule management UI**
  Status: DONE. Files: `SmsAutoResponderController`, `SmsAutoResponderService`,
  `resources/views/Admin/sms/settings/auto_responders`, `routes/admin_sms_settings.php`.

- [ ] **5.3 Inbound matching and reply**
  Status: DONE. Files: `SmsAutoResponderEngine::handleInbound()` (called from `SmsWebhookService`),
  `SmsKeywordNormalizer`, `SmsReservedCodes`. Reply is sent through `SingleSmsSendService` so balance,
  billing and audit apply. Never replies to own lines, inbound OTP traffic, reserved/opt-out words, and is
  rate-limited per sender per rule (`services.sms.auto_reply_window_minutes`). Test: `tests/Feature/SmsAutoResponderTest.php`.

- [ ] **5.4 Webhook authentication**
  Status: DONE. Files: `app/Http/Middleware/VerifySmsWebhookSecret.php`, `SmsWebhookService`.

---

## P6 - Data model changes (تغییرات مدل داده)

- [ ] **6.1 Core tables**
  Status: DONE. `sender_numbers`, `sms_message_groups`, `sms_message_templates`, `sms_panels`,
  `business_sender_number`, `otp_balances`, `advertising_balances`, `sms_message_template_histories`,
  `sms_campaigns`, `sms_messages`, `sms_transactions`, `sms_campaign_recipients`
  (migrations `2026_10_07_0000xx_*`).

- [ ] **6.2 Classification, group and category columns**
  Status: DONE. Migrations `2026_10_11_000017_add_classification_to_sms_messages_and_campaigns.php`,
  `2026_10_12_000003_add_group_and_classification_to_groupsms_table.php`,
  `2026_10_20_000001_add_otp_category_to_sms_templates_and_messages.php`.

- [ ] **6.3 Business scoping**
  Status: DONE. Migration `2026_10_12_000002_separate_categories_and_templates_per_business.php`.

- [ ] **6.4 Contract, CMS, audit, queue, top-up, auto-responder tables**
  Status: DONE. Migrations `2026_10_11_000003_create_sms_cms_contents_table.php`,
  `2026_10_16_000002_create_sms_contract_otps_table.php`, `2026_10_18_000001_*`,
  `2026_10_19_000002_*`, `2026_10_21_000001_*`, `2026_10_22_000001_*`.

- [ ] **6.5 Seeded data**
  Status: DONE. `database/seeders/SmsMessageGroupSeeder.php` (mali, hoghoghi, poshtibani, ejraei, foroush,
  monasebati and subgroups), migrations `2026_10_11_000011_*`, `..._000013_*`, `2026_10_19_000003_seed_event_holiday_sms_group_and_templates.php`.

- [ ] **6.6 Pending operational step**
  Status: PARTIAL. No new migration was added for the promotional-group restriction or the filter subset
  (both are code-only). Deploying the branch still requires running the existing unapplied SMS migrations
  in the target environment; this checklist does not run them.
