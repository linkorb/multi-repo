# LinkORB contributing guide 👨‍💻

This document provides general guidelines for making meaningful contributions to LinkORB's projects on GitHub.

## Getting started 🏇

The _**README.md**_ file at the root of each repository is a great resource for getting started with an existing project. Review it and read other related documentation (in the ***docs/** folder) if necessary.

## Making changes 🛠

Don't work directly on the `main` branch of a project. Clone the repository to your computer or open it in a Codespace and create a new branch before making changes.

### Naming a branch 🎋

An ideal branch name contains two to three descriptive words. If the branch is related to an internal project/task, terminate its name with the card number of the related Team HQ task.

**Example**

```
add-contributing-guide-4096
```

### Committing changes 🏗

LinkORB has the following requirements for committing changes to a repository:

1. Use [LinkORB's commit message template](/repo_commit.template) to summarize changes introduced by a commit. Please see [configure LinkORB's commit message template](https://engineering.linkorb.com/topics/git/articles/commit-template/) for setup instructions.
2. Use the format outlined in our [conventional commit standards](https://engineering.linkorb.com/topics/git/articles/commit-standards/) when writing commit messages.

## Submitting and reviewing changes 🚀

1. [Squash related commits into one](https://engineering.linkorb.com/topics/git/articles/squash-related-commits/) before opening a pull request or before merging a pull request into the main branch.
2. See our [Creating and reviewing pull requests](https://engineering.linkorb.com/topics/git/articles/reviewing-pr/) guide for pull request best practices.

## Testing ⛳

Test all code changes using development and _mock_ production environments to avoid unpleasant surprises.

## Reporting/discussing Issues 🚧

### Internal team members

If you're a LinkORB team member, please use one of the following channels to report bugs or vulnerabilities found in internal/closed source and open source projects:

- Create a Cyans or Mattermost topic to discuss next steps.
- Create and assign Team HQ cards to team members who can resolve the issue. See [Add a card to a project](https://engineering.linkorb.com/about/culture-handbook/project-cards/#add-a-card-to-a-project) for more information.

### External contributors

If you're a third-party contributor, please check that there's no open issue addressing the problem before creating a new GitHub issue.

## Documentation ✍

Technical writers, please review LinkORB's [technical documentation standards](https://engineering.linkorb.com/topics/technical-documentation/articles/getting-started/#technical-documentation-standards) before adding or modifying documentation in a project. If the created/modified document is a web page, run the site locally or in a Codespace to ensure it renders as expected before committing changes.

## Questions 🙋

Direct your questions about a project to the repository's primary maintainer/contributor or a subject-matter expert. See [Communicate through appropriate channels](https://engineering.linkorb.com/about/culture-handbook/first-day-and-week/#communicate-through-appropriate-channels) and [Asynchronous communications tl;dr](https://engineering.linkorb.com/about/culture-handbook/first-day-and-week/#asynchronous-communications-tldr) for communication best practices.
