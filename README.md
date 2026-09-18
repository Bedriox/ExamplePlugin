# Bedriox ExamplePlugin

This repository contains the first-party example plugin for
[Bedriox](https://bedriox.com), a Minecraft: Bedrock Edition server written in
modern PHP.

`ExamplePlugin` targets the experimental Bedriox API `0.1`. It demonstrates a
PHAR-ready manifest, lifecycle logging, attribute-based event registration,
the default `NORMAL` priority, an explicit `HIGH` cancellable listener,
read-only `MONITOR` observation, and a safe player message request.

## Example behavior

- Players receive a welcome message after joining.
- Sending the exact text `cancel me` demonstrates chat cancellation and an
  authoritative private response.
- Accepted and cancelled chat is observed at `MONITOR` priority.
- Successful block placement is logged through the plugin-bound logger.

The example does not include commands because Bedriox does not yet expose a
public command API.

## Build the PHAR

Use [Bedriox PluginTools](https://github.com/Bedriox/PluginTools):

```shell
php -d phar.readonly=0 ../PluginTools/bin/plugin-tools . dist/ExamplePlugin.phar
```

Copy `dist/ExamplePlugin.phar` into the server's `plugins/` directory and
restart Bedriox. Source directories are intentionally not loaded in production.

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
