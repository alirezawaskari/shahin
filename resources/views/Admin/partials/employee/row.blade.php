@php
    use Carbon\Carbon;
        $thisId = $employee->id;
        $parentAttribute = $parentId ?? 0;
        $children = $employee->childrenRecursive;
@endphp

<tr
    @if($level > 0)
        class="collapse child-of-{{ $parentAttribute }} level-{{ $level }}"
    @endif
    id="row-{{ $thisId }}"
    data-parent="{{ $parentAttribute }}"
>
    <td>{{ $index + 1 }}</td>
    <td>{{ $employee->user->name }}</td>
    <td>{{ $employee->user->family }}</td>
    <td>{{ $employee->user->phone }}</td>
    <td>
        @if(!empty($percentageAllocations[$employee->user_id]))
            @foreach($percentageAllocations[$employee->user_id]->unique(function($item) {
                return $item->type . '-' . $item->percent . '-' . $item->factor_sender;
            }) as $allocation)
                <div>
                    {{ \App\Models\PercentageAllocationEmployee::TYPE[$allocation->type] ?? '-' }}: {{ $allocation->percent }}%
                </div>
            @endforeach
        @else
            -
        @endif
    </td>
    <td>
        {{ optional($employee->service)->title ?? '-' }}
        |
        {{ optional($employee->center)->title ?? '-' }}
    </td>
    <td>{{ $percentageAllocations[$employee->user_id][0]->percent ?? 0 }}%</td>
    <td>{{ optional($employee->sender)->name ?? '-' }}</td>
    <td>
        {{
            $employee->contract_start_date && $employee->contract_start_date !== '0000-00-00 00:00:00'
                ? verta(Carbon::parse($employee->contract_start_date))->format('Y/m/d')
                : '-'
        }}
    </td>
    <td>
        {{
            $employee->contract_expiration_date && $employee->contract_expiration_date !== '0000-00-00 00:00:00'
                ? verta(Carbon::parse($employee->contract_expiration_date))->format('Y/m/d')
                : '-'
        }}
    </td>
    <td>{{ optional($employee->agentUser)->name ?? '-' }}</td>
    <td>
        {{
            $employee->created_at && $employee->created_at !== '0000-00-00 00:00:00'
                ? verta(Carbon::parse($employee->created_at))->format('Y/m/d')
                : '-'
        }}
    </td>

    <td>
        @if($children->isNotEmpty())
            <button class="btn btn-info" data-bs-toggle="collapse" data-bs-target=".child-of-{{ $employee->id }}">
                <i class="fa fa-plus"></i>
            </button>
        @else
            -
        @endif
    </td>
    <td>
        @php
            $allocationId = $percentageAllocations[$employee->user_id][0]->id ?? null;
        @endphp

        @if($allocationId)
            <button class="percentage_allocation_employee_delete btn btn-danger btn-floating"
                    data-id="{{ $allocationId }}"><i class="fa fa-trash"></i></button>
        @else
            -
        @endif

    </td>
</tr>

@if($children->isNotEmpty())
    @foreach($children as $child)
        @include('Admin.partials.employee.row', [
            'employee' => $child,
            'percentageAllocations' => $percentageAllocations,
            'level' => $level + 1,
            'parentId' => $thisId
        ])
    @endforeach
@endif
