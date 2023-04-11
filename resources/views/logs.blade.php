<!DOCTYPE html>
<html>
<head>
    <title>Application Logs</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/font-awesome.css')}}">

    <style>
        body {
            font-size: 22px;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }

        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 3px;
            border: 1px solid #ddd;
            white-space: pre-wrap;
            font-family: 'Monaco', 'Menlo', 'Consolas', monospace;
            margin: 20px 0;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h1 class="text-center">Application Logs</h1>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if(empty($errorLines))
                <div class="alert alert-info text-center my-5">No logs available.</div>
            @else
                <div class="card">
                    <div class="card-header bg-light text-dark">
                        <h5 class="mb-0">Errors</h5>
                        <a style="float: right" href="#footer">
                            <h5>got to footer </h5>
                        </a>
                    </div>
                    <div class="card-body">
                        @foreach ($errorLines as $line)
                            @if(preg_match_all('/^\[([\d]{4}-[\d]{2}-[\d]{2})/', $line))
                                <pre class="border border-secondary bg-info ">[Error Title] </pre>
                                <pre style="border: solid 3px #0c5460" >{{ $line }}</pre>
                            @elseif($line == '[stacktrace]')
                            <pre class="border border-secondary bg-info ">{{ $line }}</pre>
                            @else
                                <pre>{{ $line }}</pre>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
    <span id="footer"></span>
</div>
</body>
</html>
