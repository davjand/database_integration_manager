Database Integration Manager
============================

- Version: 0.1
- Authors: David Anderson (dave@veodesign.co.uk) & Tom Johnson (jetbackwards@gmail.com)
- Website: http://veodesign.co.uk
- Github: https://github.com/davjand/database_integration_manager/
- Build Date: 2013-03-08
- Requirements: Symphony 2.3

## Release Notes

Version 0.1
- Initial Release
- This extension is still in beta so use with caution
- Do use for a live site just yet, it hasn't been tested with anything outside of localhost deployment
- Please see https://github.com/davjand/database_integration_manager/issues for known issues


## About

Symphony's database structure doesn't currently lend its self to a continuous integration workflow. There are ways to do it as evidenced by some other great extensions but from bitter experience, it is very easy to accidentally corrupt your database. And this is just for simple workflows with one developer, it gets even more complicated with several developers in which if queries are executed in the wrong order, you get some horrible database issues.

Database Integration Manager (DIM) was designed to *simply* solve these problems for my workflow which assumes the following setup:

1. Development occurs on a locally installed version of symphony
2. A **staging** server (or testing server) 
3. A **live** server

*NB: DIM doesn't require both (2) & (3), a single server can be used*

While not necessary, the following makes for an optimised workflow

4. The entire site is setup as a github repository
5. Post Commit hooks to update the server repositories on commit via a simple build script
6. The simple build script invokes DIM to perform updates on the staging server

## How it works

DIM provides a method of locking the database structure when a user is making changes. Once the changes are completed, no further users can edit the database structure until they are running the latest version. Workflow is something like as follows:

### Example Workflow

1. DIM is setup and configured on the staging server (server mode)
2. DIM is setup and configured on the local install (client mode)
3. Authentication details are setup on the server and local installs (multi-user support)

*The database structure is completely locked now. No pages, events or sections can be edited. NB: Database entries can still be created/edited/deleted etc.*

4. The local user requests a database *checkout* which is recorded on the staging server

*At this point the user can make changes to their local symphony install. If another user tries to checkout then this will be denied*

5. The local user adds a section and *checks in* the database, creating a new version.

*At this point the new version is logged on the staging server but not complete as the version only exists locally. The version file must be uploaded to the staging server (My workflow uses GIT to do this).*

6. Once the staging server has been updated to the latest version, then (4) can happen again.


## Installation

1. Download source code into extensions/database_integration_manager folder
2. Visit System/Extensions and install
3. Configure the extension as prompted (see configuration).

### Using Git

> git submodule add git@github.com:davjand/database_integration_manager extensions/database_integration_manager


## Configuration

You must configure the staging server first as the client will only configure with a valid staging server.

(A) If you have been developing locally then setup the install on a web accessible server and clone the database to it.
(B) If you have been developing remotely then do the opposite of *A*

### Staging Server

1. Install the extension and configure as prompted to do so.
2. On the staging server, set the mode to *Server* and configure at least one user

### Local Install

1. Install the extension and configure as prompted to do so
2. Enter the staging server url. This should point to the root symphony install. *(NB localhost can be accessed 127.0.0.1/YOUR_FOLDER)*
3. Enter the user credentials and save.
4. You're ready to go!


### Advanced, GIT/Deployment

The extension stores data in the following places, these are currently not configurable

1. tbl_dim_versions in the database
2. /manifest/dim
3. /data

The manifest/dim folder should be *gitignored* as the local configuration and caching is stored here.
The data folder should not be *gitignored* as this is used to store the version files.

To perform an update from a deploy script the url that needs to be called is as follows:

> /extension/database_integration_manager/server/index.php?action=update&email={USER EMAIL}&auth-key={AUTH KEY}


## FAQ

### 1) Is this any use for single developer projects?

Yes! Utilising the workflow above, DIM can be used as a deploy tool (in conjunction with git) to allow you to develop and test locally and then push changes live with a single git commit.

### 2) I only have a live server, I don't need any of this 'staging' business!?

The extension only needs a single web facing server to be used. Simply configure as described above - where ever this read me tells you to configure the staging server, perform these actions on your live server

### 3) What do I do if a developer checks out the database and doesn't check it back in?

Call them and tell them to check it in! This extension has been developed to overcome the practicalities of database deployment and provide a decent safety net, not to solve people's communication skills!

On a serious note, this functionality has been mentioned in development and could feasibly be implemented.

### 4) Can the extension use existing author data to authenticate?

Not currently, the authentication is completely independent.




