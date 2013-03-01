<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = new Silex\Application();
$today = date("Y-m-d");

$app['debug'] = false;

// Register Twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/views',
));

//Register Url binding to controllers
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

// Obtain list of scrum days
$scrums = scandir(__DIR__ . '/database');
if (isset($scrums)) {
    $archive = array();
    foreach ($scrums as $scrum) {
        if ($scrum != '.' && $scrum != '..' && $scrum != 'projects') {
            $title = $scrum;
            if ($scrum == date("Y-m-d")) {
                $title = 'Today';
            }
            $archive[] = array(
                "id" => $scrum,
                "title" => $title
            );
        }
    }
    $archiveLinks = array_reverse($archive);
}

// Obtain list of used projects for autocomplete
$projects = file_get_contents(__DIR__ . '/database/projects');
$rows = explode(PHP_EOL, $projects);
$autocompleteData = array();
foreach ($rows as $project) {
    if ($project != '') {
        $autocompleteData[] = '"' . $project . '"';
    }
}
$autocompleteData = array_unique($autocompleteData);
$autocomplete = implode(',', $autocompleteData);

// Index page
$app->match('/', function () use ($app, $archiveLinks, $autocomplete) {
    return $app['twig']->render('index.twig', array(
        'archiveLinks' => $archiveLinks,
        'autocomplete' => $autocomplete,
    ));
})
->bind('home');

// Status page
$app->match('/status', function () use ($app, $today) {
    $file = $today;

    $date = ($_POST['date']) ? $_POST['date'] : $_GET['date'];

    if ($date) {
        $file = $date;
    }

    if (file_exists(__DIR__ . '/database/' . $file)) {
        $entries = file_get_contents(__DIR__ . '/database/' . $file);
    } else {
        $entries = "<b>No entries found.</b>";
    }

    return $app['twig']->render('status.twig', array(
        'entries' => $entries,

    ));
})
->bind('status');

// Load scrum page
$app->match('/count', function () use ($today) {
    $file = $today;

    if (file_exists(__DIR__ . '/database/' . $file)) {
        $entryData = file_get_contents(__DIR__ . '/database/' . $file);
        $entries = explode(PHP_EOL, $entryData);

        echo "Entries logged: " . (count($entries) - 1);
    }
    exit;
})
->bind('count');

// Load scrum page
$app->match('/load', function () use ($today) {
    $file = $today;

    $date = ($_POST['date']) ? $_POST['date'] : $_GET['date'];

    if ($date) {
        $file = $date;
    }

    if (file_exists(__DIR__ . '/database/' . $file)) {
        echo file_get_contents(__DIR__ . '/database/' . $file);
    } else {
        echo "<b>No entries found.</b>";
    }
    exit;
})
->bind('load');

// Save scrum status entry
$app->match('/save', function () use ($today) {
    $name = ($_POST['name']) ? $_POST['name'] : $_GET['name'];
    $project = ($_POST['project']) ? $_POST['project'] : $_GET['project'];
    $txt1 = ($_POST['txt1']) ? $_POST['txt1'] : $_GET['txt1'];
    $txt2 = ($_POST['txt2']) ? $_POST['txt2'] : $_GET['txt2'];

    $entryData = "<section class=\"entry\"><h2>$name @ $project</h2><p><b>I am working on: </b> $txt1</p><p><b>Problems: </b> $txt2</p></section>\n";
    $projectData = "$project\n";

    $entryFile = __DIR__ . '/database/' . $today;
    $projectFile = __DIR__ . '/database/projects';

    file_put_contents($entryFile, $entryData, FILE_APPEND);

    // Only save new project name if it doesn't already exist
    $projectsData = file_get_contents(__DIR__ . '/database/projects');
    $usedProjects = explode(PHP_EOL, $projectsData);
    if (!in_array($project, $usedProjects)) {
        file_put_contents($projectFile, $projectData, FILE_APPEND);
    }

    // Display entries for current date
    echo file_get_contents($entryFile);
    exit;
})
->bind('save');

$app->run();
