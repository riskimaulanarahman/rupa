@include('errors.layout', [
    'code' => 503,
    'title' => __('errors.status.503.title'),
    'description' => __('errors.status.503.description'),
    'icon' => 'wrench',
    'showDetail' => false,
])
