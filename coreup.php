<html>
  <head><title>RA Core Updates</title>
  <link type="text/css" rel="stylesheet" media="all" href="style.css">
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
    $filename = "json_files/" . $_POST['fileloader'];
    $handle = fopen($filename, 'r');
    $_POST['loader'] = fread($handle, filesize($filename));
  }

  if (isset($_POST['loader'])) {
    $data = json_decode($_POST['loader'], True);
    if (isset($_POST['save'])) {
      $handle = fopen($_POST['save'], 'w');
      fwrite($handle, $_POST['loader']);
    }
  } else {
    $data = array();
    $data['ticket'] = postgrab("ticket");
    $data['repo'] = postgrab("repo");
    $data['testURL'] = postgrab("testurl");
    $data['distro'] = postgrab("distro");
    $data['sourceVersion'] = postgrab("sourceversion");
    $data['targetVersion'] = postgrab("targetversion");
    $data['sourceDatabase'] = postgrab("sourcedb");
    $data['acquiaWorkflow'] = postgrab("acquiaworkflow");

  if ($data['repo'] && strpos($data['repo'],'@') === false) {
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
      $data['targetBranch'] = 'acq-upd-' . $data["ticket"] . '-' . date('Ymd');
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
     include 'catconfig.php';
  }

  ?>
<pre>
<?php // print_r($_POST); ?>
</pre>
<h1>RA core updates: <?php echo($data['site']); ?></h1>
<div id="json">
  <form name="load" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" id="jsonform">
    <h2> JSON Values: </h2>
    <textarea name="loader" rows="20" cols="50">
      <?php print json_encode($data); ?>
    </textarea>
    <br />
    <input type="submit" value="Insert values">
    </form>

    <br />
    <form name="loadfile" action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
    <input name="fileloader" type="file">
    <br />
    <input type="submit" value="Load from file">
    </form>

    <?php if ($data['site'] && $data['ticket']) : ?>
      <br />
      <form name="savefile" action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
      <input type="text" name="save" size="35" value="json_files/<?php echo join('-', array($data['site'], $data['ticket'], date('Ymd'))); ?>.json">
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
      <label>Source Branch:</label> <input type="text" name="sourcebranch" value="<?php echo $data['sourceBranch']; ?>"><br />
      <label>Target Branch:</label> <input type="text" name="targetbranch" value="<?php echo $data['targetBranch']; ?>"><br />
      <label>Revision: (svn only)</label> <input type="text" name="svnrev" value="<?php echo $data['svnRev']; ?>"></p>
      </div>
      <div id="two">
      <label>Source Version:</label> <input type="text" name="sourceversion" value="<?php echo $data['sourceVersion'] ?>"><br />
      <label>Target Version:</label> <input type="text" name="targetversion" value="<?php echo $data['targetVersion'] ?>"><br />
      <label>Distro: </label> <input type="text" name="distro" value="<?php echo $data['distro'] ?>"><br />
      <label>Source DB:</label> <input type="text" name="sourcedb" value="<?php echo $data['sourceDB']; ?>"><br />
      <label>Target DB:</label> <input type="text" name="targetdb" value="<?php echo $data['targetDB']; ?>"><br />
      </div>
      <p><input type="submit" name="standard" value="Submit"></p>
      </fieldset>
      <?php if ($data['vcs'] == 'svn') : ?>
        <fieldset class="svn">
        <legend>Create branch</legend>
        <h2>Checkout the repo</h2>
        <p>cd <?php echo $data['clientDirectory'] ?> <br />
        mkdir <?php echo $data['site']?> <br />
        cd <?php echo $data['site']?> <br />
        <p>svn checkout --username <?php echo $data['svnUsername'] ?> --password <?php echo $data['svnPassword'] ?> \ <br />
        <em><?php echo $data["repo"]?>/<?php echo $data['sourceBranch'] ?></em></p>
        <p>ENTER REVISION NUMBER ABOVE</p>
        <p>cd <?php echo $data['sourceBranch'] ?></p>

        <h2>Create a branch</h2>
        <p>svn cp \ <br />
        <?php echo $data["repo"]?>/<em><?php echo $data['sourceBranch']?></em> \ <br />
        <?php echo $data["repo"]?>/branches/<em><?php echo $data['targetBranch']; ?></em> \ <br />
        -m "<?php echo $data['advisorInitials']?>@acquia, Ticket #15066-<em><?php echo $data['ticket']?></em>: Branch from <?php echo $data['sourceBranch']?>
        to implement update from <em><?php echo $data['distro']?></em> <em><?php echo $data['sourceVersion'] ;?></em> to <em><?php echo $data["targetVersion"]?></em>." </p>

        <p>svn switch ^/branches/<?php echo $data['targetBranch']. "\n";  ?></p>
        <p>cd docroot</p>
        <p>patch -p1 < ~melissa/patches/<?php echo(trim(strtolower($data['distro']))). "\n"; ?></p>

        <p>svn status --no-ignore | grep rej</p>

        <p>svn status --no-ignore | grep orig</p>

        <p>svn status | grep '\?' | awk '{print $2}' | xargs svn add</p>
        <p>svn status | grep '\!' | awk '{print $2}' | xargs svn rm</p>
        <p>cleanversion.sh</p>
        <p>svn commit -m "<?php echo $data['advisorInitials'] ?>@acquia, Ticket #15066-<?php echo $data['ticket']?>: Update from <?php echo $data['distro'] ?> <?php echo $data['sourceVersion'] ?> to <?php echo $data['targetVersion'] ?>."
  </p>
      </fieldset>
    <?php endif; ?>
       <?php if ($data['vcs'] == 'git') : ?>
        <fieldset class="svn">
        <legend>Create branch</legend>
          <h2>Create a repo for the first time</h2>
            <p>cd <?php echo $data['clientDirectory'] ?></p>
            <p>mkdir <?php echo $data['site']; ?></p>
            <p>cd <?php echo $data['site']; ?></p>
            <p>git clone <?php echo $data['repo']; ?></p>
            <p>cd <?php echo $data['site']; ?></p>
          <h2>Modify existing local repo</h2>
            <p>cd <?php echo $data['clientDirectory'] ?>/<?php echo $data['site']; ?>/<?php echo $data['site']; ?></p>
          <h2>Checkout a new branch</h2>
            <p>git checkout -b <?php echo $data['targetBranch']; ?></p>
            <p>cd docroot</p>
            <p>patch -p1
                <?php echo $data['patchDirectory'] ?>/<?php echo(trim(strtolower($data['distro']))); ?>/<?php echo(trim(strtolower($data['distro']))); ?>-<?php echo $data['sourceVersion'] ?>_to_<?php echo $data['targetVersion'] ?>.patch
            </p>
            <p>git status | grep rej</p>
            <p>git status | grep orig</p>
            <p><?php echo $data['patchDirectory'] ?>/scripts/rmv-versionnums-dpl.sh</p>
            <p>git add -A</p>
            <p>git commit -m "<?php echo $data['advisorInitials']?>@acquia, Ticket #15066-<?php echo $data['ticket']?>: Update from <?php echo $data['distro'] ?> <?php echo $data['sourceVersion'] ?>_to_<?php echo $data['targetVersion'] ?>."</p>
            <p>git push --set-upstream origin <?php echo $data['targetBranch'] ?></p>
          </fieldset>
        <?php endif; ?>



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

The updated code is available at:
<?php echo $data['testURL'] . "\r"; ?>
Please test and let me know if you see any issues.

When I receive your approval, I will merge with <?php echo $data['sourceBranch']; ?>, create a tag,
refresh the DB from <?php echo $data['sourceDB']; ?> and ask you to test one more time before deploying to to production.
      </textarea><br />
        <label id="rev">Tag:</label> <input type="text" name="tag" value="<?php echo $data['tag']; ?>">
          <p><input type="submit" name="updatetag" value="Update tag">
          <input type="submit" name="savetag" value="Save tag"></p>
     </fieldset>

    <?php if ($data['vcs'] == 'svn') : ?>
    <fieldset class="svn">
      <legend id="<?php echo $updatetag?>">Merge</legend>
        <p>cd ~melissa/www/ra/<?php echo $data['site']; ?>/trunk</p>
         <p>svn info</p>
          <p>svn switch ^/<?php echo $data['sourceBranch']; ?></p>
          <p>svn up</p>
          <p>svn log -v -l 20 ^/branches/<?php echo $data['targetBranch']; ?>| grep "A /branch" </p>
          <p>svn merge ^/branches/<?php echo $data['targetBranch']; ?> -r<?php echo $data['svnRev']; ?>:HEAD</p>
          <p>svn commit -m "<?php echo $data['advisorInitials']; ?>@acquia, Ticket #15066-<?php echo $data['ticket']; ?>: Merged branches/<?php echo $data['targetBranch']; ?> to <?php echo $data['sourceBranch']; ?>" </p>
          <p>svn cp
          <?php echo $data['repo']; ?>/<?php echo $data['sourceBranch']; ?>
          <?php echo $data['repo']; ?>/tags/<?php echo $data['tag']; ?>
          -m "<?php echo $data['advisorInitials']; ?>@acquia, Ticket #15066-<?php echo $data['ticket']; ?>: Tag to deploy <?php echo $data['sourceVersion']; ?> to <?php echo $data['targetVersion']; ?> update to production"</p>
      </fieldset>
    <?php endif; ?>

    <?php if ($data['vcs'] == 'git') : ?>
      <fieldset class="git">
        <legend>Merge</legend>
        <p>cd <?php echo $data['clientDirectory'] ?>/<?php echo $data['site']; ?>/<?php echo $data['site']; ?></p>
        <p>git branch</p>
        <p>git checkout <?php echo $data['sourceBranch']; ?></p>
        <p>git pull</p>
        <p>git merge <? echo $data['targetBranch'];?></p>
        <p>git tag -a <?php echo $data['tag']; ?> -m "<?php echo $data['advisorInitials']; ?>@acquia, Ticket #15066-<?php echo $data['ticket']; ?>: Tag to deploy <?php echo $data['sourceVersion']; ?> to <?php echo $data['targetVersion']; ?> update to production"</p>
        <p>git push</p>
        <p>git push origin tag <?php echo $data['tag']; ?></p>

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
