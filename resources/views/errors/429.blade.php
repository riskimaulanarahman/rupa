@include('errors.layout', [
    'code' => 429,
    'title' => __('errors.status.429.title'),
    'description' => __('errors.status.429.description'),
    'icon' => 'bolt',
    'showDetail' => true,
])
