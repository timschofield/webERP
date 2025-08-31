# Development Workflow

Thank you to (Andrew Couling)[https://github.com/andrewcouling] for providing this workflow description.

The webERP project uses the Git distributed version control system (DVCS) and the project repository is hosted on GutHub. There are many workflows for using Git and GitHub, but we recommend the procedure here to at least get you started.

* For more information on using Git, refer to the (official Git documentation)[https://git-scm.com/doc].

* For webERP developer information, refer to the (webERP project Wiki)[https://github.com/timschofield/webERP/wiki]

## You need a GitHub account

webERP uses GitHub to manage our code. If you haven't already signed up, you need to create a GitHub account at
https://www.github.com. This is free, and simple to do.

You will also need to have the "git distributed software configuration management (SCM) system installed on your local
computer. There are versions for most operating systems available (from here)https://git-scm.com/downloads.

## Fork the webERP project repository

On the GitHub site, navigate to the webERP project repository (https://github.com/timschofield/webERP), and click on the
'Fork' button at the top right area of the page (as shown in the image below).

![screenshot of github webpage](github.avif)

This makes a copy of that repository into your own github account and is where you will upload changes (you can only
commit to your own repository).

Next, clone your fork of the project repository to your computer for development.

## Clone your GitHub fork

1. Go to your GitHub account page

2. Go to your fork of the webERP repository, e.g. https://github.com/YOURUSERNAME/webERP

3. On the right side of the page, you should see a button labelled "<> Code". Click on the button.

   ![screenshot of github webpage](CodeButton.avif)

4. The pop-up box which appears will display a URL. Click on the icon next to the URL field, to copy the URL to your clipboard.

   ![screenshot of github webpage](CodeURL.avif)

There are many applications that interface with the Git system, and you are urged to find the one that suits you best.
For the sake of this tutorial we will use the command line, and SourceTree.
Now switch to the desktop application of choice and use that URL to clone the repository locally:

### Command line

1. Pick a working directory on your PC

2. cd into that directory

3. Type: `git clone PASTE-THAT-URL-HERE`. (note the . at the end, which says "put it in the current directory")

### SourceTree

1. Choose File, New Clone...

2. In the Source Path/URL, paste the URL you copied from GitHub

3. For the destination path, pick a folder on your PC

4. For the bookmark name, call it whatever friendly name you want to remember this repo by. It will show up in SourceTree's
   bookmarks list of repositories you've got.

5. Click Clone or OK to have it start the clone. It will take a few seconds for it to download the repository contents to
   your computer.

## Add upstream remote

To keep your own fork up-to-date, you'll need to periodically merge updates from the main webERP repository. This involves
telling your own local (on your PC) git repository about the main webERP repository location. To do this you must add what's
called an "upstream remote".

### Command line

1. cd into the directory of the repository you're adding the remote to

2. Type: `git remote add upstream https://github.com/timschofield/webERP.git`

3. Type: `git fetch upstream`

###   SourceTree

1. Choose Repositories from the main menu

2. Choose Repository Settings...

3. Click Add

4. For Remote Name, use: upstream

6. For the URL use: https://github.com/timschofield/webERP.git

7. For the GitHub username, enter your own GitHub account name

8. Click OK

9. Click Fetch

##   Create a branch to work in

Any time you're going to contribute code changes, you'll want to first make a working branch. (For more on branches,
see the documentation on the Git web site.) Branches are how git keeps different versions of changes separate from each
other until such time as someone approves merging them together into the main branch.

Decide on a branch name. The branch name should be brief, but meaningful; ideally a max of 6 words, all hyphenated, no
spaces. E.g. "v5-update-edi" (update edi for v5 release).

Finally create and checkout your new branch and start work. Using a "working" or "feature" branch groups the work and
simplifies reviews. keeps your y to work in. This keeps your all your commits together and you can push the branch to your fork so others can see, before you issue a PR to merge the work into the master branch.

### Command line

1. cd into the directory of the repository you're intending to make changes to

2. In this example we'll be branching from the 'master' branch

3. Type: `git branch name-of-your-new-branch-here master`

4. Type: `git checkout name-of-your-new-branch-here`

### SourceTree

1. First, make sure you're in "Log View" (View, Log View)

2. Find where it shows upstream/master, and right-click on that row. Choose Branch... from the pop-up menu

3. Give it the new branch name

4. Leave the "checkout new branch" box checked

5. Click Create Branch or OK

## Create or modify code

1. Edit or create whatever files are applicable to the changes you wish to submit for consideration.

2. Test your code. Test to make sure your changes work, and that you've not broken anything else in the process.

3. Once your code is ready for submission, you'll need to commit the changes, and push them to your GitHub account and
   then create a Pull Request. Those steps are described below.

4. Make sure your code complies with the webERP coding standards, else it may be rejected.

5. If your code can be tested with phpunit, be sure to include those tests in your commits and pull request.

## Commit your changes

To commit your code, you must first "stage" the files which are to be included. See the git docs mentioned at the top of
this page for more detailed explanation of what this means.

Once you've staged the files, then you commit them, which saves that group of changes together.

You can make multiple commits (that is, stage the files and commit them) towards any given issue. This allows you to make
numerous smaller commits which are easily described in connection with the specific files that relate to those smaller changes.

### Commit message

1. The "subject" or "first line" of a commit message should be no more than 50 characters.

2. The next lines can have as much detail as you like. Consider using GitHub Markdown syntax for any formatting you might
   wish to include in the message. Feel free to use blank lines, and even use hyphens to create bulleted lists (hyphen plus a space)

3. If you're contributing code to help with an "Issue" that's already listed on the webERP GitHub Issues page, include
   that issue number in your commit message, with the hashtag in front of it, like this: `#101` for issue number 101.

4. Further to the point above, if your commit "fixes" or "closes" or "resolves" an existing open issue then include the
   word "Fixes" before the issue number, ie: "Fixes #101" somewhere in your commit message. This will cause GitHub to
   close the "issue" ticket when your pull request is merged, and helps keep things tidy.

5. If you're committing code that addresses a bug reported on the webERP support forum, include the URL for that bug
   from the forum, so we can cross-reference it.

We suggest reading 7 Principles for Good Commit Messages.

### Command line

1. cd into the directory of the repository you're committing from

2. Type: `git status` (This will give you a list of changed/added/deleted files)

3. Type: `git add filename1.php filename2.php` (and any other files, etc)

4. Type: `git commit` (This will pop up your text editor where you can supply a commit message. See explanation of
   commit messages in the subsection below)

5. Save the message using whatever method your text editor uses to save-and-exit

6. This will have the commit saved locally. You can continue working and making more commits until you're ready to push
  them all up to GitHub (see pushing commits below)

### SourceTree

1. First, go into File Status view. Click on "Working Copy" in the left nav menu under File Status. Or, use the View menu
   and choose File Status View. Here you'll see a list of files on-screen which have changed in some way (edits, adds,
   deletes). You can also see exactly what's changed by clicking on those files and viewing the "diff" on the other side
   of the screen.

2. For each file that you wish to include in the current commit, highlight it in the bottom part of the window, and click
   the Stage Selected button on the button-bar. It may ask you to confirm that you wish to Add it. (The Windows version
   has a tiny up-arrow that lets you do the staging as well, instead of using the Add from the top button-bar).

3. Once your files are all staged, click the Commit button in the button bar

4. This will open a dialog where you can supply a commit message. See the guidance around commit messages in the next
   section below.

5. Click the commit button in the bottom right. Your commit is now saved locally on your PC. You can continue making more
   commits until you're ready to push them all to GitHub, as described below.

## Push your commit to your GitHub fork

Now that you've made some commits to git on your local PC, you must push them to (your account on) GitHub in order to
prepare to share them.

### Command line

1. cd into the directory of your working repository

2. Type: `git push origin name-of-my-working-branch`

### SourceTree

1. Click the Push button in the top button bar.

2. From the pulldown for "Push to repository", be sure that "origin" is selected. That's your GitHub repository, and you
   must push to there.

3. Next make sure you check the box next to the branch you've been making your commits in. Uncheck all the others.

4. Click OK

5. That's it! Now all the commits you've made in that branch on your PC will show up in your GitHub account.

## Send the project a pull request

(you'll often see the shorthand PR used instead of "Pull Request")

After you've pushed your working branch (ie: containing your new commits) to your own GitHub account, you will need to
create a Pull Request in order to ask the webERP developer team to review it and consider it for inclusion in the next release.

Using your web browser,

1. Go to your GitHub account.

2. Go to your webERP fork.

3. You will see a green "Compare and Pull Request" button. Click it. (If it's been several hours since you did the push,
   it might not show the green bar. In that case, click the 'Branches' link, where you will see a Pull Request button
   next to each of your branches. Click the one next to the branch you want to do the pull request from.)

4. Now you can review the collection of commits and file changes, and add a descriptive message to the pull request. If
  you're fixing something that's already got an open issue for it, be sure that the issue number is included in your Pull
  Request message. ie: `#101`. If you believe your Pull Request fully fixes the open issue, then say "Fixes #101",
  as the keyword "Fixes" helps do proper cleanup of tickets once closed. There may be multiple branches within the
  'upstream remote' webERP repository, so ensure that your pull request "compare" is indeed being compared to the intended branch.

5. Click the next green button to Create Pull Request

6. Now you wait for others to review your code. The webERP developers (and anyone else who has clicked "Watch" on the
  main webERP repository) will get an alert about the pull request. Anyone wishing to reply with their opinions of what
  you've submitted can engage in dialog with you and one another while the code is reviewed.

7. If your code hasn't complied with the coding standards, or has bugs or is incomplete, you may be asked to submit more
  commits to rectify the problems. In that case, you will repeat the steps above for making code changes, making commits,
  and pushing those commits to GitHub. As long as you push to the same branch on GitHub, then all those commits will automatically be included in the pull request, so reviewers can see the updates you push.

8. Once the webERP development team decides what to do with it, they have basically three options: to accept it (merge
  the pull request) or defer it (until a later date) or reject it (not merge it and close the pull request). GitHub will
  automatically email you about all updates and comments made about your Pull Request.

## Keep your local branch current

When you or others make pull requests that are accepted into the webERP repository, that will make your own local copy
be outdated. To keep current, you must periodically bring in the changes from the "upstream remote" we created earlier.
(see: https://help.github.com/articles/syncing-a-fork/)

## Command line

Type: `git fetch upstream`

Type: `git checkout master` (ie: if you're going to pull changes from the master branch)

Type: `git merge upstream/master` (merges your local copy with the upstream remote)

Type: `git push` (brings your forked repository up-to-date)

### SourceTree

Click the Pull button in the top button bar

For Pull From Repository, choose "upstream" from the pulldown menu

For Remote Branch To Pull, choose "master"

Leave the "commit merged changes immediately" box checked, and the others unchecked.

Click OK

## Clean up old branches and grab new branches

From time to time you and others will add or remove branches from the GitHub repositories, and you will want to keep your
PC in sync with those.

### Command line

Type: `git fetch upstream`

### SourceTree

Click the Fetch button on the button bar

There are 3 checkboxes. Check them all. (You could opt to not prune/delete any local branches you've created, if you want
to preserve them to understand your own work history, by unchecking the corresponding box.) (You could also fetch from
individual remotes manually, and prune only when fetching from upstream, but never prune when fetching from your own GitHub master)

Click OK
