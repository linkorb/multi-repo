Multi-Repo
==========
Multi repo is a command line tool which allows you to manage multiple git repositories at a time and apply to them some specific rules (such as: add qa checks, configure CI, normalize config files, ensure vendor packages).

## Installation

    git clone git@github.com:linkorb/multi-repo.git
    cd multi-repo
    docker build . --tag linkorb-repo:0.1 --file Dockerfile
    docker run -d --rm -t -i -v=${PWD}:/app --name linkorb-repo-php linkorb-repo:0.1
    docker exec linkorb-repo-php composer install # install PHP dependencies

## Configuration

To specify list of repositories and define rules `repos.yaml` file used. See `repos.yaml.dist` to get an idea on that config. It might look like that:
```yaml
parameters:
  repos:
    defaults: ~
    configs:
      myAwesomeRepo:
        gitUrl: 'git@github.com:linkorb/your-repo-name.git'
        variables: ~
        fixers:
          circleci:
            template: "%kernel.project_dir%/templates/.circleci/config.yml.twig"
          qaChecks:
            checks:
              - "phpcs"
              - "phpstan"
          githubWorkflows:
            templates:
              ".github/workflows/production.yml": "https://raw.github.com/…./production.yml.twig"
              ".github/workflows/staging.yml": "https://raw.github.com/…./staging.yml.twig"
```

## Run command
To execute command please run `/app/bin/console linkorb:multi-repo:fix`. It's possible to run over specific repo only or apply a specific fixer. For more options take a look on: `/app/bin/console linkorb:multi-repo:fix --help` 

## Flow
* Once you'll run the "fix" command, the tool will iterate over all repositories, do `git clone` (if needed) and `git pull` on default branch (which is master in most of the cases). 
* After the command finishes execution, you can go to repositories under `./repositories/source` directory, review, commit & push changes.
* If you want to have an overview of repositories which will contain any changes, please run: `/app/bin/console linkorb:multi-repo:list-uncommitted`

## The repositories base path
* By default, `repositories/` in this project's directory is used as the repository base path.
  You can change this to a different location by specifying a custom absolute path in `MULTI_REPO_REPOSITORIES_PATH` in your `.env.local` file
* You can also specify custom `repos.yaml` location. For that you need to specify `MULTI_REPO_CONFIG_PATH` (defaults: under root dir). 
  It must be either absolute path or relative from root dir and ends up with config file name.
* You can have following hierarchy of metadata (priority of overriding in the same order):
  * metadata section in mains `repos.yaml` config
  * metadata section in `repo.yaml` config (located relative to main `repos.yaml` as `./repos/${REPOSITORY_NAME}/repo.yaml`)
  * metadata section in `repo.yaml` config in destination repository
* The repositories base path directory is structured in the following way: 
  * one directory per configured repository (optionally with sub-directories), which contains an actual state of each repository, changes, etc.
  * `.multi-repo-cache` directory which is meant to be used for restoring initial data in case of exception during an execution

## Miscellaneous
* To access protected url as a template for fixer, etc. you need to define in `.env.local` following parameters:
    * HTTP_CLIENT_AUTHORIZATION_TYPE (`token` for github access tokens)
    * HTTP_CLIENT_AUTHORIZATION_VALUE
