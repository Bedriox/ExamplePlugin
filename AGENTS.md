# Bedriox ExamplePlugin contributor guide

## Purpose and boundary

This repository is the first-party example for Bedriox's public plugin API. It
must demonstrate only contracts implemented and documented by a released
Bedriox API version. It is teaching material, not a second server framework and
not a place to expose server, Protocol, RakNet, or Data internals.

Do not add placeholder calls, guessed class names, or roadmap features to an
executable example. The code must remain within the implemented experimental
API declared by `plugin.json`.

## Repository structure

- `README.md` states the current usable boundary and links to Bedriox.
- `composer.json` owns the PHP toolchain and repository verification commands.
- `tests/` protects repository and example contracts.
- `plugin.json` declares the supported API and PHAR entry point.
- `src/` contains only public-API example code.
- `tests/Stubs/Api/` mirrors only the small API surface required for isolated
  repository CI; integration qualification still uses the real Bedriox API.
- `.github/workflows/ci.yml` runs the supported PHP matrix.

Do not commit `vendor/`, credentials, access tokens, server runtime data,
player data, logs, crash reports, packet captures, or Minecraft assets.

## Implementation rules

- Target 64-bit PHP 8.4 through PHP 8.x and begin every PHP source file with
  `declare(strict_types=1);`.
- Use the public Bedriox plugin API only. Never depend on internal server
  classes, mutable registries, sockets, packets, encryption state, queues, or
  process-local block-state IDs.
- Pin the compatible experimental API deliberately. Update the manifest,
  Composer dependency, README, tests, and documentation together.
- Keep event listeners small and deterministic. Respect server authority;
  cancellation may reject valid intent but cannot bypass authentication,
  validation, reach, collision, inventory ownership, world bounds, or limits.
- Use the plugin-bound logger. Never log tokens, JWT contents, encryption
  material, credentials, raw packets, or unrelated personal information.
- Treat configuration and every player-controlled value as untrusted and
  bounded.
- Do not imply PHP plugins are sandboxed. They are trusted code in the server
  process.
- Demonstrate commands and other features only after their public API exists;
  keep examples within the released sender, permission, and input boundary.

## Change safety

Before editing, state the intended files, observable teaching outcome, public
API revision being demonstrated, and behavior that must remain unchanged. A
public example may not lead the server contract. If the example exposes an API
gap, change and verify the owning Bedriox repository first, then update this
repository in a separate coordinated change.

Existing examples are cumulative compatibility tests. A behavior change needs
a focused test, migration explanation, and corresponding public Docs update.
Do not patch `vendor/` or use a dirty sibling checkout as release evidence.

## Verification

Use Composer 2 and PHP `^8.4`:

```shell
composer install
composer check
composer validate --strict
composer audit --locked
```

Before a commit, inspect `git status --short`, the complete diff,
`git diff --check`, and all generated dependency changes. When the example
depends on Bedriox, also run its integration test against the pinned API and
the applicable workspace gate.

## Licensing and documentation

Original work is GPL-3.0-only. Do not copy code or data with unknown or
incompatible rights. Preserve legally required notices. Public plugin guides
belong in the Docs repository; this README remains a concise installation and
example entry point.

Keep commits focused and natural. Do not include personal email addresses or
identity trailers.

## Definition of done

A change is complete when it uses only released public API, demonstrates one
clear supported behavior, includes success and failure tests, passes the full
local gate, keeps documentation synchronized, preserves security and ownership
boundaries, and contains only intended licensed files.
