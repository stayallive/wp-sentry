## Contributing

First off, thank you for considering contributing to WordPress Sentry. 

### 1. Where do I go from here?

If you've noticed a bug or have a question 
[search the issue tracker](https://github.com/stayallive/wp-sentry/issues?q=something)
to see if someone else in the community has already created a ticket.
If not, go ahead and [make one](https://github.com/stayallive/wp-sentry/issues/new)!

### 2. Fork & create a branch and submit a PR

If this is something you think you can fix, then
[fork WordPress Sentry](https://help.github.com/articles/fork-a-repo)
and create a branch with a descriptive name.

A good branch name would be (where issue #64 is the ticket you're working on):

```sh
git checkout -b 64-fix-errors-not-reporting
```

After pushing this to your fork you can [create a pull request](https://help.github.com/articles/creating-a-pull-request-from-a-fork/) to contribute your changes.

## Note on updating Sentry ~Raven~ JS SDK

If you plan on updating the Sentry JS SDK please not we added a few lines to the bottom of the minified file that need to stay and also we remove the reference to the sourcemap.
