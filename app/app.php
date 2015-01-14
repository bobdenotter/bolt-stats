<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * "root"
 */
$app->get("/aggregate", function(Silex\Application $app) {

    $twigvars = array();

    $twigvars['title'] = "Silex skeleton app";

    $twigvars['content'] = "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation.";

    // Clear the stats.
    $statement = $app['db']->prepare("DELETE FROM stats");
    $statement->execute();

    $year = "2012";
    $month = "9";

    $currentdate = date("Y-m");

    $loop = sprintf("%04d-%02d", $year, $month);

    while($loop <= $currentdate) {
        // echo "<p>$loop - $currentdate";

        $row = array(
            'month' => $loop
        );

        // Get the _new_ installs
        $query = 'select COUNT(*) FROM newshits where LEFT(datecreated, 7) = "' . $loop . '"';
        $row['newinstalls'] = doQuery($app, $query, 'COUNT(*)');

        // Get the _total_ installs
        $query = 'select COUNT(*) FROM newshits where LEFT(datecreated, 7) <= "' . $loop . '"';
        $row['totalinstalls'] = doQuery($app, $query, 'COUNT(*)');

        // Get the _seen_ installs
        $query = 'select COUNT(*) FROM newshits where LEFT(datelastseen, 7) = "' . $loop . '"';
        $row['seen'] = doQuery($app, $query, 'COUNT(*)');

        // dump($row);

        $app['db']->insert('stats', $row);

        // Update the month.
        if (++$month == 13) { 
            $month = 1; 
            $year++; 
        }
        $loop = sprintf("%04d-%02d", $year, $month);

    }

    
    return $app['twig']->render('index.twig', $twigvars);


});

$app->get("/", function(Silex\Application $app) {

    $twigvars['title'] = "Charts";

    return $app['twig']->render('charts.twig', $twigvars);

});



$app->get('/chart', function (Silex\Application $app, Request $request) {

    $what = $request->get('what');


    $stmt = $app['db']->executeQuery("SELECT * FROM stats");

    $rows = $stmt->fetchAll();

    $twigvars = array(
        'rows' => $rows,
        'what' => $what
    );

    if ($request->get('total') == true) {
        $template = 'graph_total.twig';
    } else {
        $template = 'graph.twig';

    }

    return $app['twig']->render($template, $twigvars);


});


function doQuery($app, $query, $key)
{

    $statement = $app['db']->prepare($query);
    $statement->execute();
    $result = $statement->fetch();
//    $row['seen'] = $res['COUNT(*)'];


    return $result[$key];
}
