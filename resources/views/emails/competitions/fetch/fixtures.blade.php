@extends('emails.layouts.app')
@section('content')
    <h2 class="text-gray-700 dark:text-gray-200">{{ $data['name'] }}</h2>

    <p style="font-size:16px">
        The website fetches competition fixtures in groups of {{ $data['chunk'] }}, utilizing memory-efficient techniques to
        handle large datasets by retrieving and processing records in smaller, manageable portions.
    </p>

    @foreach ($data['competitions_and_teams'] as $key => $competitions_and_teams)
        <h4>Competition: {{ $competitions_and_teams['competition'] }}</h4>
        <ul>
            @foreach ($competitions_and_teams['teams'] as $team)
                @php
                    if (!is_array($team)) {
                        '<li>Not array</li>';
                    }
                    continue;
                @endphp
                <li>
                    <table>
                        <tr>
                            <th colspan="2">{{ $team['team'] }}</th>
                        </tr>
                        @foreach ($team['fetch_details'] as $key => $value)
                            <tr>
                                <td colspan="2">
                                    {{ $value['name'] }}, {{ $value['counts'] }} times.
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="2"><b>Fixtures count:</b> {{ count($team['fetch_details']) }}</td>
                        </tr>
                    </table>
                </li>
            @endforeach
        </ul>
    @endforeach

    <p class="mt-2 text-gray-600 dark:text-gray-300 font-bold text-lg">
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
