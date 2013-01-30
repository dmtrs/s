<!DOCTYPE html>

<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
    <meta charset="utf-8" />

    <!-- Set the viewport width to device width for mobile -->
    <meta name="viewport" content="width=device-width" />

    <title><?php echo $name;?></title>

    <!-- Included CSS Files (Uncompressed) -->
    <!--
    <link rel="stylesheet" href="stylesheets/foundation.css">
    -->

    <!-- Included CSS Files (Compressed) -->
    <link rel="stylesheet" href="css/foundation.min.css">
    <link rel="stylesheet" href="css/app.css">
    <script type="text/x-handlebars" data-template-name="application">
    Test
    </script>
    <script src="js/jquery.js"></script>
    <script src="js/handlebars-1.0.rc.2.js"></script>
    <script src="js/ember-1.0.0-pre.4.min.js"></script>
    <script src="js/modernizr.foundation.js"></script>
</head>
<body>
    <div class="row">
        <div class="twelve columns">
            <h2><?php echo $name; ?></h2>
        <hr />
    </div>

    <!-- Included JS Files (Compressed) -->
    <script src="js/foundation.min.js"></script>
</body>
</html>
