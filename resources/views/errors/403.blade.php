@include('errors.layout', [
    'code' => 403,
    'title' => __('errors.status.403.title'),
    'description' => __('errors.status.403.description'),
    'icon' => 'shield',
    'showDetail' => true,
])
