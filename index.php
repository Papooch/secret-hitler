<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" type="text/css" href="css/hitler.css">

    <title>Secret Hitler</title>
    
    <script type="text/javascript" src="../jquery-3.4.1.min.js"></script>
    <script type="text/javascript" src="js/ajax.js"></script>
    <script type="text/javascript" src="js/main.js"></script>
    <script type="text/javascript" src="js/components.js"></script>
    <script type="text/javascript" src="js/drawing.js"></script>
    <script>
        var $_GET = <?php echo json_encode($_GET); ?>;
    </script>
</head>
<body id="body">
    <div id="content">
        Loading...
    </div>
<script>
    $(document).ready(main())
</script>
</body>
</html>