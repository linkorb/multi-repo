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
To execute command please run `/app/bin/console linkorb:multi-repo:fix`. It's possible to run over specific repo only or apply specific fixer. For more options take a look on: `/app/bin/console linkorb:multi-repo:fix --help` 
