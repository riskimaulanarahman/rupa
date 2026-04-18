@php
    use Illuminate\Support\Facades\Lang;

    $status = isset($exception) && method_exists($exception, 'getStatusCode')
        ? (int) $exception->getStatusCode()
        : 500;

    if ($status < 500 || $status > 599) {
        $status = 500;
    }

    $titleKey = "errors.status.{$status}.title";
    $descriptionKey = "errors.status.{$status}.description";
@endphp

@include('errors.layout', [
    'code' => $status,
    'title' => Lang::has($titleKey) ? __($titleKey) : __('errors.status.5xx.title'),
    'description' => Lang::has($descriptionKey) ? __($descriptionKey) : __('errors.status.5xx.description'),
    'icon' => 'server',
    'showDetail' => false,
])
