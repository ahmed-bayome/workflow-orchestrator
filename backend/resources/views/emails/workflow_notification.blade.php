<!DOCTYPE html>
<html>
<head>
    <title>Workflow Notification</title>
</head>
<body>
    <h1>Workflow Notification: {{ $type }}</h1>
    
    @if(isset($data['step_name']))
        <p>Step: {{ $data['step_name'] }}</p>
    @endif
    
    @if(isset($data['request_id']))
        <p>Request ID: {{ $data['request_id'] }}</p>
    @endif
    
    <p>Please check the application for more details.</p>
</body>
</html>
