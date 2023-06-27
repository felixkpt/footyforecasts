@extends('emails.layouts.app')
@section('content')
    <h2 class="text-gray-700 dark:text-gray-200">{{ $data['name'] }}</h2>

    <p style="font-size:16px">Competitions fixtures fetch completion notification {{ date('Y-m-d H:i:s') }}</p>
    
    <p class="mt-2 leading-loose text-gray-600 dark:text-gray-300 font-bold text-lg">
        {{ $data['message'] }}
    </p>
    <p class="mt-2 text-gray-600 dark:text-gray-300">
        Please review and ensure the smooth functioning of this feature.
    </p>
    <p class="mt-2 text-gray-600 dark:text-gray-300">
        Best regards, <br>
        Felix Biwott Team
    </p>
    <a href="{{ URL::to('') }}"
        class="px-6 py-2 mt-8 text-sm font-medium tracking-wider text-white capitalize transition-colors duration-300 transform bg-blue-600 rounded-lg hover:bg-blue-500 focus:outline-none focus:ring focus:ring-blue-300 focus:ring-opacity-80">
        Visit site
    </a>
@endsection
