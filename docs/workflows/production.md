# Running SatisPress in Production

When managing a single site or keeping each project self-contained is the end goal, it's possible to set up the production site as the package server.

## Benefits

* No additional infrastructure needs to be managed
* Each site is self-contained

## Concerns

* Developers and CI/CD servers may generate additional load on the live site
* Packages are cached on the live site, so the may need to be excluded from backups and pruned now and then
* Deployments need to be atomic. A CI/CD server should be able to download all packages before a new version is deployed. Ideally uploads would not be stored within the deployment path.

[Back to Index](../index.md)
