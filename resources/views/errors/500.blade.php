@include('errors.layout', [
    'code' => 500,
    'title' => __('errors.status.500.title'),
    'description' => __('errors.status.500.description'),
    'icon' => 'server',
    'showDetail' => false,
])
