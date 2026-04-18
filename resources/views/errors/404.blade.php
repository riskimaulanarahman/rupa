@include('errors.layout', [
    'code' => 404,
    'title' => __('errors.status.404.title'),
    'description' => __('errors.status.404.description'),
    'icon' => 'compass',
    'showDetail' => true,
])
