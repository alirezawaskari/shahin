@if(($msg->direction ?? 'outbound') === 'inbound')
    <span class="badge badge-soft-info">{{ \App\Support\SmsLabels::direction('inbound') }}</span>
@else
    <span class="badge badge-soft-primary">{{ \App\Support\SmsLabels::direction('outbound') }}</span>
@endif
