@php
    use Illuminate\Support\Facades\Lang;

    $status = isset($exception) && method_exists($exception, 'getStatusCode')
        ? (int) $exception->getStatusCode()
        : 400;

    if ($status < 400 || $status > 499) {
        $status = 400;
    }

    $titleKey = "errors.status.{$status}.title";
    $descriptionKey = "errors.status.{$status}.description";
@endphp

@include('errors.layout', [
    'code' => $status,
    'title' => Lang::has($titleKey) ? __($titleKey) : __('errors.status.4xx.title'),
    'description' => Lang::has($descriptionKey) ? __($descriptionKey) : __('errors.status.4xx.description'),
    'icon' => 'shield',
    'showDetail' => true,
])
