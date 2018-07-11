# Central Package Server

Agencies, development teams, or individuals that manage multiple websites may want to run a central package server to reduce some maintenance overhead. It can also make sense if the production site(s) is locked down for security or privacy reasons and doesn't check for updates.

To run a central package server, WordPress would be installed on a separate domain or subdomain and be dedicated to package management.

## Benefits

* Production can be locked down for increased security and privacy
* Developers and CI/CD servers won't generate extra load on the production site
* Packages can be shared across many different sites

## Concerns

* Licenses must be activated on the central repository rather than production sites, so an additional license may be required
* Performance of the package server may become slow depending on the number of packages managed
* Additional infrastructure that needs to be managed

[Back to Index](../Index.md)
