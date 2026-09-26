# Changelog

All notable ExamplePlugin changes are recorded here.

## Unreleased

### Added

- Demonstrate owner-scoped custom mob registration, bounded lifecycle state,
  authoritative plugin spawning, transactional controller intents, catalog
  appearances, and typed entity spawn and interaction events.
- Add a plugin-owned shapeless crafting recipe, pre-craft policy, committed
  craft observation, and repository coverage.
- Migrate the example commands to the class-based typed API and demonstrate a PHP backed-enum argument through `exampledisplay`.
- Add the player-only `exampledisplay` command covering every high-level Bedriox text and display method.
- Add the API `0.1` PHAR-ready example with lifecycle logging, typed join,
  chat, and block events, event priorities, cancellation, observation, and
  safe player messaging.
- Add a harmless `examplesender` command that demonstrates console and player
  sender discrimination through the public command API.
- Document PluginTools source loading and the primary `makeplugin` console
  workflow, including overwrite protection and the standalone CLI alternative.
