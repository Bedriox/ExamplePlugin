# Changelog

All notable ExamplePlugin changes are recorded here.

## Unreleased

- Target Bedriox plugin API 0.4 and demonstrate packaged configuration
  resources, customizable join announcements, and global broadcasts.

### Changed

- Target Bedriox plugin API `0.3`, use the session-bound `Player` methods for
  private event responses and main, armor, and offhand inventory capabilities
  instead of routing actions through `Server`, and retain the player's
  generation-bound world when building a custom-mob spawn position.

### Added

- Observe committed furnace processing and player experience changes through
  typed API 0.3 post-events without mutating authoritative state.
- Demonstrate typed player effects, committed effect events, and targeted world particles through `exampleeffect`.
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
