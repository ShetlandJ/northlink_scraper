<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Da Spaektionary is Shetland's online dictionary for the Shaetlan language, where users can add words, comments and recordings!">

        <title inertia>{{ config('app.name', 'Spaektionary') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
        <!-- <link rel="stylesheet" href="{{ mix('css/app.css') }}"> -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet" type="text/css" >

        <!-- Scripts -->
        @routes
        <script src="{{ mix('js/app.js') }}" defer></script>
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia

        @env ('local')
            <script src="http://localhost:3000/browser-sync/browser-sync-client.js"></script>
        @endenv
    </body>
</html>

<style>
.availability-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
  }

  /* colourblind-friendly colours */
.red {
    background-color: #DB4325;
  }

  .orange {
    background-color: #EDA247;
  }

  .yellow {
    background-color: #FFC20A;
  }

  .green {
    background-color: #57C4AD;
  }
  .today {
    width: 25px;
    height: 25px;
    border: solid rgb(50, 50, 50) 1px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    margin-bottom: 3px;
}

</style>