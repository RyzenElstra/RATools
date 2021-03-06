<html>
  <head><title>RA Core Updates</title>
  <link href="http://code.jquery.com/ui/1.10.0/themes/ui-lightness/jquery-ui.css" rel="stylesheet">
  <script src="http://code.jquery.com/jquery-1.9.0.js"></script>
  <script src="http://code.jquery.com/ui/1.10.0/jquery-ui.js"></script>
      <link type="text/css" rel="stylesheet" media="all" href="style.css">
  <script>
  $(function() {

    $( ".accordion" ).accordion({
      heightStyle: "content",
      collapsible: true
    });
    $( ".button" ).button();
    $( "#datepicker" ).datepicker({
      inline: true
    });

  });
  </script>
    </head>
  <body>
  <?php

  function postgrab($post) {
    if (isset($_POST[$post])) {
      return $_POST[$post];
    }
  }

  if (postgrab("updatetag") || postgrab("savetag")) {
    $updatetag = 'jump';
    if (postgrab("updatetag")) {
      unset($_POST['tag']);
    }
  } else {
    $updatetag = 'updatetag';
  }

  if (isset($_POST['fileloader']) && !isset($_POST['loader'])) {
    include_once('catconfig.php');
    $filename = $data['clientDirectory'] . '/logs/json_files/' . $_POST['fileloader'];
    $handle = fopen($filename, 'r');
    $_POST['loader'] = fread($handle, filesize($filename));
  }
  if (isset($_POST['loader'])) {
    $json = stripslashes($_POST['loader']);
    $data = json_decode($json, True);
    if (isset($_POST['save'])) {
      $handle = fopen($_POST['save'], 'w');
      fwrite($handle, $json);    }
  } else {
    $data = array();
    $data['ticket'] = postgrab("ticket");
    $data['repo'] = postgrab("repo");
    $data['testURL'] = postgrab("testurl");
    $data['acquiaDomains'] = postgrab("acquiadomains");
    $data['distro'] = postgrab("distro");
    $data['sourceVersion'] = postgrab("sourceversion");
    $data['targetVersion'] = postgrab("targetversion");
    $data['sourceDatabase'] = postgrab("sourcedb");
    $data['acquiaWorkflow'] = postgrab("acquiaworkflow");
    $data['notes'] = postgrab("notes");

  if ($data['repo'] && strpos($data['repo'],'@') === false) {
    include 'catconfig.php';
    //Get the site name (svn)
      $site = explode('/', $data["repo"]);
      $data['site'] = $site[count($site)-1];
      $data['vcs'] = 'svn';
    } elseif (strpos($data['repo'],'@') !== false) {
      $site = explode('@', $data["repo"]);
      $data['site'] = $site[0];
      $data['vcs'] = 'git';
    } else {
      $data['vcs'] = '';
      $data['site'] = '';
    }

    // Clear if ticket wasn't set when the branch was generated
    $targetBranch = postgrab("targetbranch");
    if ($targetBranch && $targetBranch != '-' . date('Ymd')) {
      $data['targetBranch'] = $targetBranch;
    } elseif (postgrab("ticket")) {
      $data['targetBranch'] = 'acqUpd-' . $data["ticket"] . '-' . date('Ymd');
    } else {
      $data['targetBranch'] = '';
    }
    if (postgrab("svnrev")) {
     $data['svnRev'] = postgrab("svnrev");
    } else {
      $data['svnRev'] = '';
    }
    if (postgrab("tag")) {
      $data['tag'] = postgrab("tag");
    } else {
      $data['tag'] = date("Y-m-d");
    }
    if (postgrab("testurl")) {
      $data['testURL'] = postgrab("testurl");
    } else {
      $data['testURL'] = '';
    }
    if (postgrab("acquiadomains")) {
      $data['acquiaDomains'] = postgrab("acquiadomains");
    } else {
      $data['acquiaDomains'] = '';
    }
    if (postgrab("sourcebranch")) {
      $data['sourceBranch'] = postgrab("sourcebranch");
    } elseif ($data['vcs']) {
      if (postgrab("vcs") == 'git') {
        $data['sourceBranch'] = 'master';
      } else {
        $data['sourceBranch'] = 'trunk';
      }
    } else {
      $data['sourceBranch'] = '';
    }
    if (postgrab("sourcedb")) {
      $data['sourceDB'] = postgrab("sourcedb");
    } else {
      $data['sourceDB'] = "Prod";
    }
    if (postgrab("targetdb")) {
      $data['targetDB'] = postgrab("targetdb");
    } else {
      $data['targetDB'] = "Stage";
    }
    if (postgrab("notes")) {
      $data['notes'] = postgrab("notes");
    } else {
      $data['notes'] = "";
    }
  }

  ?>
<pre>
<?php // print_r($_POST); ?>
</pre>
<h1>RA core updates: <?php echo($data['site']); ?></h1>
<div class="accordion" id="json">
   <h2> Load a Form: </h2>
    <form name="loadfile" action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
    <input name="fileloader" type="file">
    <br />
    <input type="submit" value="Load from file" class="button">
    </form>
    <h2> JSON Values: </h2>
  <form name="load" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" id="jsonform">
    <textarea name="loader" rows="20" cols="50">
      <?php print json_encode($data); ?>
    </textarea>
    <br />
    <input type="submit" value="Insert values">
    </form>

    <?php if ($data['site'] && $data['ticket']) : ?>
     <h2> Save a Form: </h2>
      <form name="savefile" action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
      <input type="text" name="save" size="35" value="<?php echo $data['clientDirectory'] . '/logs/json_files/' . join('-', array($data['site'], $data['ticket'], date('Ymd'))); ?>.json">
      <input type="hidden" name="loader" value="<?php print htmlentities(json_encode($data)); ?>">
      <br />
      <input type="submit" value="Save to file">
    <?php endif; ?>
    </form>
</div>

<div id="branch">
   <fieldset>
   <legend>Gather data</legend>
   <form name="input" action="<?php $_SERVER['PHP_SELF'] ?>#jump" method="post">
      <h2>Values</h2>
      <div id="one">
        <label>Ticket (15066-):</label> <input type="text" name="ticket" value="<?php echo $data['ticket']?>"><br />
        <label>Acquia Workflow:</label> <input type="text" name="acquiaworkflow" value="<?php echo $data['acquiaWorkflow']?>"> <a href="<?php echo $data['acquiaWorkflow']?>" target="_blank">Visit</a><br />
        <label>Repo URL:</label> <input type="text" name="repo" value="<?php echo $data['repo']?>"><br />
        <label>URL to test:</label> <input type="text" name="testurl" value="<?php echo $data['testURL']?>"> <a href="<?php echo $data['testURL']?>" target="_blank">Visit</a><br />
        <label>Domains Page:</label> <input type="text" name="acquiadomains" value="<?php echo $data['acquiaDomains']?>"> <a href="<?php echo $data['acquiaDomains']?>" target="_blank">Visit</a><br />
        <label>Source Branch:</label> <input type="text" name="sourcebranch" value="<?php echo $data['sourceBranch']; ?>"><br />
        <label>Target Branch:</label> <input type="text" name="targetbranch" value="<?php echo $data['targetBranch']; ?>"><br />
      </div>
      <div id="two">
        <label>Revision: (svn only)</label> <input type="text" name="svnrev" value="<?php echo $data['svnRev']; ?>"></p>
        <label>Source Version:</label> <input type="text" name="sourceversion" value="<?php echo $data['sourceVersion'] ?>"><br />
        <label>Target Version:</label> <input type="text" name="targetversion" value="<?php echo $data['targetVersion'] ?>"><br />
        <label>Distro: </label> <input type="text" name="distro" value="<?php echo $data['distro'] ?>"><br />
        <label>Source DB:</label> <input type="text" name="sourcedb" value="<?php echo $data['sourceDB']; ?>"><br />
        <label>Target DB:</label> <input type="text" name="targetdb" value="<?php echo $data['targetDB']; ?>"><br />
      </div>
      <p></p>
      <div class="accordion"><h3>Add Notes:</h3>
        <textarea cols="100" rows="20"  name="notes"><?php echo htmlentities($data['notes']) ?></textarea><br />
     </div>
      <p><input type="submit" name="standard" value="Submit"></p>
      </fieldset>
      <?php if ($data['vcs'] == 'svn') : ?>
        <fieldset class="svn">
        <legend>Create branch</legend>
          <h2>Checkout trunk for the first time</h2>
            <ol>
              <li>cd <?php echo $data['clientDirectory'] ?></li>
              <li>mkdir <?php echo $data['site']; ?></li>
              <li>cd <?php echo $data['site']; ?></li>
              <li>svn checkout --username <?php echo $data['svnUsername'] ?> --password <?php echo $data['svnPassword'] ?> <?php echo $data["repo"]?>/<?php echo $data['sourceBranch'] ?></li>
              <li>cd <?php echo $data['sourceBranch'] ?></li>
            </ol>
          <h2>Modify existing local repo</h2>
            <ol>
              <li>cd <?php echo $data['clientDirectory'] ?>/<?php echo $data['site']; ?>/trunk</li>
            </ol>
        <h2>Create a branch</h2>
            <ol>
              <li>svn cp <?php echo $data['repo']; ?>/<?php echo $data['sourceBranch']?>  <?php echo $data['repo']; ?>/branches/<?php echo $data['targetBranch']; ?> -m "<?php echo $data['advisorInitials']?>@acquia, Ticket #15066-<?php echo $data['ticket']?>: Branch from <?php echo $data['sourceBranch']?> to implement update from <?php echo $data['distro']?> <?php echo $data['sourceVersion'] ;?> to <?php echo $data["targetVersion"]?>." </li>
              <li>ENTER REVISION NUMBER ABOVE</li>
              <li>svn switch ^/branches/<?php echo $data['targetBranch']. "\n";  ?></li>
              <li>cd docroot</li>
              <li>patch -p1 <
                <?php echo $data['patchDirectory'] ?>/<?php echo(trim(strtolower($data['distro']))); ?>/<?php echo(trim(strtolower($data['distro']))); ?>-<?php echo $data['sourceVersion'] ?>_to_<?php echo $data['targetVersion'] ?>.patch</li>
              <li>svn status --no-ignore | grep rej</li>
              <li>svn status --no-ignore | grep orig</li>
              <li>Make manual modifications as necessary.  Be sure to remove all *.rej and *.orig files when manual modifications are complete.</li>
              <li>svn status | grep '\?' | awk '{print $2}' | xargs svn add</li>
              <li>svn status | grep '\!' | awk '{print $2}' | xargs svn rm</li>
              <li><?php echo $data['patchDirectory'] ?>/scripts/rmv-versionnums-dpl.sh</li>
              <li>svn commit -m "<?php echo $data['advisorInitials'] ?>@acquia, Ticket #15066-<?php echo $data['ticket']?>: Update from <?php echo $data['distro'] ?> <?php echo $data['sourceVersion'] ?> to <?php echo $data['targetVersion'] ?>."</li>
            </ol>
      </fieldset>
    <?php endif; ?>
       <?php if ($data['vcs'] == 'git') : ?>
        <fieldset class="git">
        <legend>Create branch</legend>
          <h2>Create a repo for the first time</h2>
            <ol>
              <li>cd <?php echo $data['clientDirectory'] ?></li>
              <li>mkdir <?php echo $data['site']; ?></li>
              <li>cd <?php echo $data['site']; ?></li>
              <li>git clone <?php echo $data['repo']; ?></li>
              <li>cd <?php echo $data['site']; ?></li>
            </ol>
          <h2>Modify existing local repo</h2>
            <ol>
              <li>cd <?php echo $data['clientDirectory'] ?>/<?php echo $data['site']; ?>/<?php echo $data['site']; ?></li>
            </ol>
          <h2>Checkout a new branch</h2>
             <ol>
                <li>git pull origin master</li>
                <li>git checkout <?php echo $data['sourceBranch']; ?></li>
                <li>git checkout -b <?php echo $data['targetBranch']; ?></li>
                <li>cd docroot</li>
                <li>patch -p1 <
                    <?php echo $data['patchDirectory'] ?>/<?php echo(trim(strtolower($data['distro']))); ?>/<?php echo(trim(strtolower($data['distro']))); ?>-<?php echo $data['sourceVersion'] ?>_to_<?php echo $data['targetVersion'] ?>.patch</li>
                <li>git status | grep rej</li>
                <li>git status | grep orig</li>
                <li>Make manual modifications as necessary.  Be sure to remove all *.rej and *.orig files when manual modifications are complete.</li>
                <li><?php echo $data['patchDirectory'] ?>/scripts/rmv-versionnums-dpl.sh</li>
                <li>git add -A</li>
                <li>git commit -m "<?php echo $data['advisorInitials']?>@acquia, Ticket #15066-<?php echo $data['ticket']?>: Update from <?php echo $data['distro'] ?> <?php echo $data['sourceVersion'] ?> to <?php echo $data['targetVersion'] ?>."</li>
                <li>git push --set-upstream origin <?php echo $data['targetBranch'] ?></li>
              </ol>
          </fieldset>
        <?php endif; ?>
    <fieldset id="database">
      <legend>Update Database</legend>
        <ol>
          <li>Backup <?php echo $data['targetDB']; ?> DB.</li>
          <li>Copy <?php echo $data['sourceDB']; ?> DB to <?php echo $data['targetDB']; ?>.</li>
          <li>aht @<?php echo $data['site']; ?>.test drush --uri=default updb</li>
        </ol>
    </fieldset>
    <fieldset id="client">
      <legend>Tell the client</legend>
        <textarea cols="100" rows="20" name="customerbranchnote">
I have done the following:
Created the branch <?php echo $data['targetBranch']; ?> from <?php echo $data['sourceBranch'] . "\r"; ?>
Updated the code locally
Backed up <?php echo $data['targetDB']; ?> DB
Copied the <?php echo $data['sourceDB']; ?> DB to <?php echo $data['targetDB'] . "\r"; ?>
Deployed <?php echo $data['targetBranch']; ?> on <?php echo $data['targetDB'] . "\r" ; ?>
Updated the DB

Please test the updated code and let me know if you see any issues.  The updated site is available at: <?php echo $data['testURL'] . "\r"; ?>.  You may see all dev, stage and production links here: <?php echo $data['acquiaDomains'] . "\r"; ?>

When I receive your approval, I will merge with <?php echo $data['sourceBranch']; ?>, create a tag, refresh the DB from <?php echo $data['sourceDB']; ?> and ask you to test one more time before deploying to to production.
      </textarea><br />
        <label id="rev">Tag:</label> <input type="text" name="tag" value="<?php echo $data['tag']; ?>">
          <p><input type="submit" name="updatetag" value="Update tag">
          <input type="submit" name="savetag" value="Save tag"></p>
     </fieldset>

    <?php if ($data['vcs'] == 'svn') : ?>
    <fieldset class="svn">
      <legend id="<?php echo $updatetag?>">Merge</legend>
        <ol>
          <li>cd <?php echo $data['clientDirectory'] ?>/<?php echo $data['site']; ?>/trunk</li>
          <li>svn info</li>
          <li>svn switch ^/<?php echo $data['sourceBranch']; ?></li>
          <li>svn up</li>
          <li>svn log -v -l 20 ^/branches/<?php echo $data['targetBranch']; ?> | grep "A /branch" </li>
          <li>svn merge ^/branches/<?php echo $data['targetBranch']; ?> -r<?php echo $data['svnRev']; ?>:HEAD</li>
          <li>svn commit -m "<?php echo $data['advisorInitials']; ?>@acquia, Ticket #15066-<?php echo $data['ticket']; ?>: Merged branches/<?php echo $data['targetBranch']; ?> to <?php echo $data['sourceBranch']; ?>.  Includes update from <?php echo $data['sourceVersion'] ?> to <?php echo $data['targetVersion'] ?>."</li>
          <li>svn cp
          <?php echo $data['repo']; ?>/<?php echo $data['sourceBranch']; ?>
          <?php echo $data['repo']; ?>/tags/<?php echo $data['tag']; ?>
          -m "<?php echo $data['advisorInitials']; ?>@acquia, Ticket #15066-<?php echo $data['ticket']; ?>: Tag to deploy <?php echo $data['sourceVersion']; ?> to <?php echo $data['targetVersion']; ?> update to production."</li>
        </ol>
    </fieldset>
    <?php endif; ?>

    <?php if ($data['vcs'] == 'git') : ?>
      <fieldset class="git">
        <legend>Merge</legend>
        <ol>
          <li>cd <?php echo $data['clientDirectory'] ?>/<?php echo $data['site']; ?>/<?php echo $data['site']; ?></li>
          <li>git branch</li>
          <li>git checkout <?php echo $data['sourceBranch']; ?></li>
          <li>git pull</li>
          <li>git merge <? echo $data['targetBranch'];?></li>
          <li>git tag -a <?php echo $data['tag']; ?> -m "<?php echo $data['advisorInitials']; ?>@acquia, Ticket #15066-<?php echo $data['ticket']; ?>: Tag to deploy <?php echo $data['sourceVersion']; ?> to <?php echo $data['targetVersion']; ?> update to production"</li>
          <li>git push</li>
          <li>git push origin tag <?php echo $data['tag']; ?></li>
        </ol>
      </fieldset>
    <?php endif; ?>
    <fieldset>
      <legend>Tell the client</legend>
        <textarea name="mergecomment" cols="150" rows="20">
I have merged branch <?php echo $data['targetBranch']; ?> into <?php echo $data['sourceBranch']; ?>, created the tag <?php echo $data['tag']; ?>, and deployed to <?php echo $data['targetDB']; ?> with the latest copy of the <?php echo $data['sourceDB']; ?> DB.  Please test the merged and tagged code.  Once you approve, we can coordinate deploying to production.
        </textarea>
      </div>
    </form>
  </body>
</html>
