# Contributing

Bedriox ExamplePlugin is currently in private incubation. Authorized
contributors should keep changes focused on accurately teaching a supported
public Bedriox plugin contract.

Do not invent APIs or implement roadmap features in advance. If the example
reveals a missing server contract, make and verify that change in the owning
repository first, then update this repository against its pinned version.

Every change should state its scope, the public API behavior demonstrated,
tests performed, documentation impact, and any compatibility or security
implication. Run `composer check`, strict Composer validation, the locked
dependency audit, and relevant Bedriox integration checks before committing.

Use focused commit messages without personal email addresses or identity
trailers. By contributing, you confirm that you have the right to submit the
work under GPL-3.0-only. Do not copy code or data whose license is unknown or
incompatible.
