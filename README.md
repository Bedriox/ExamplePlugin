# Bedriox ExamplePlugin

This repository contains the first-party example plugin for
[Bedriox](https://bedriox.com), a Minecraft: Bedrock Edition server written in
modern PHP.

`ExamplePlugin` targets the experimental Bedriox API `0.1`. It demonstrates a
PHAR-ready manifest, lifecycle logging, attribute-based event registration,
the default `NORMAL` priority, an explicit `HIGH` cancellable listener,
read-only `MONITOR` observation, a safe player message request, class-based
typed commands, PHP backed-enum arguments, typed command values, senders, and
the high-level player display API, plus a runtime crafting recipe.

## Example behavior

- Players receive a welcome message after joining.
- Sending the exact text `cancel me` demonstrates chat cancellation and an
  authoritative private response.
- Accepted and cancelled chat is observed at `MONITOR` priority.
- Successful block placement is logged through the plugin-bound logger.
- One dirt block can be crafted into one grass block through the plugin-owned
  `exampleplugin:grass_block_from_dirt` shapeless recipe.
- A cancellable pre-craft listener limits that example recipe to 16 repetitions
  per request, while a `MONITOR` listener observes committed crafts.
- `examplesender` reports whether its caller is the server console or a player.
- `exampledisplay` demonstrates messages, popups, jukebox popups, tips, titles,
  subtitles, action bars, toast notifications, and title clear/reset behavior.

`examplesender` accepts either sender type and does not change player or world
state. `exampledisplay` is player-only and declares its display modes through
a PHP backed enum, which Bedriox uses for validation, usage, and client
autocomplete. For example:

```text
/exampledisplay title
/exampledisplay actionbar
/exampledisplay toast
```

## Run the source plugin

Install `PluginTools.phar`, then copy or clone this project as a direct child of
the server's `plugins/` directory:

```text
plugins/
|-- PluginTools.phar
`-- ExamplePlugin/
    |-- plugin.json
    `-- src/
```

Restart Bedriox. PluginTools automatically discovers and loads the source
project; Bedriox itself continues to load only PHAR plugins. Restart again
after changing PHP source because loaded classes cannot be safely replaced.

From the Bedriox console, verify the command example:

```text
examplesender
```

With a player, place one dirt block in the personal or crafting-table grid and
craft the advertised grass-block result. A request for more than 16 repetitions
is cancelled as one transaction and receives the plugin's private explanation;
smaller committed crafts are observed through the post-event listener.

## Build the PHAR

With the source project loaded through
[PluginTools](https://github.com/Bedriox/PluginTools), run the primary build
workflow from the Bedriox console:

```text
makeplugin ExamplePlugin
```

The signed archive and checksum are written to:

```text
plugin_data/PluginTools/ExamplePlugin.phar
plugin_data/PluginTools/ExamplePlugin.phar.sha256
```

An existing build is preserved. Replace it explicitly with:

```text
makeplugin ExamplePlugin --overwrite
```

For CI, automation, or an offline server, the standalone CLI remains available:

```shell
php -d phar.readonly=0 ../PluginTools/bin/plugin-tools . dist/ExamplePlugin.phar
```

Copy the resulting PHAR into `plugins/` and remove or move the source directory
before restarting, because a source and PHAR plugin cannot share the same name.
PluginTools is development tooling and should normally be removed from a
production server.

## Development

Requirements:

- 64-bit PHP 8.4 or later within the PHP 8 major line
- Composer 2

Install the development tools and run the repository gate:

```shell
composer install
composer check
```

See [CONTRIBUTING.md](CONTRIBUTING.md) before proposing a change and
[SECURITY.md](SECURITY.md) for private vulnerability reporting.

## License

Bedriox ExamplePlugin is free and open-source software licensed under the
[GNU General Public License v3.0 only](LICENSE).

Bedriox is an independent project and is not affiliated with or endorsed by
Mojang Studios or Microsoft. Minecraft is a trademark of Microsoft
Corporation.
