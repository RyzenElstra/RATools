<?php
    if (postgrab("advisorInitials")) {
      $data['advisorInitials'] = postgrab("advisorInitials");
    } else {
      $data['advisorInitials'] = "INIATILS_HERE";
    }
    /* Acquia SVN password.*/
    if (postgrab("svnPassword")) {
      $data['svnPassword'] = postgrab("svnpassword");
    } else {
      $data['svnPassword'] = "SNV_PASSWORD";
    }
    /* Acquia SVN username.*/
    if (postgrab("svnUsername")) {
      $data['svnUsername'] = postgrab("svnUsername");
    } else {
      $data['svnUsername'] = "acquia_ahsupport_SVNUSERNAME";
    }
    /* Local path to location of all client folders ('/Users/USERNAME/Sites/clients') */
    if (postgrab("clientDirectory")) {
      $data['clientDirectory'] = postgrab("clientDirectory");
    } else {
      $data['clientDirectory'] = "CLIENT_DIRECTORY";
    }
    /* Local checkout path ('/Users/USERNAME/Sites/releases/version-patches') to clone of https://github.com/acquiacat/Drupal-Core-Git-Patches */
    if (postgrab("patchDirectory")) {
      $data['patchDirectory'] = postgrab("patchDirectory");
    } else {
      $data['patchDirectory'] = "PATCH_DIRECTORY";
?>
