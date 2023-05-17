> **_NOTE:_** This program is under ongoing development, not ready to use. Ignore the rest of the Readme

<img src="./assets/images/OrbitLogo_500.png" alt="Orbit" width="100"/>

# Orbit

A [Docker](https://www.docker.com/) and [PHP](https://www.php.net/)-[Symfony](https://symfony.com) based application, using [FFmpeg](https://ffmpeg.org/) to transcode video files.

# Project description
The objective of this project is to develop an optimized video transcoding and streaming system. This system will be capable of handling video files of different formats and sizes, converting them into a specific format, and generating .m3u8 stream files to facilitate seamless internet streaming.

The system has been designed to allow concurrent processing of multiple video files. It utilizes a combination of open-source tools and custom scripts to handle the transcoding processes efficiently.

The workflow of the system begins with users uploading video files to a designated server directory. In the future, this process can be accomplished through a user-friendly web interface. Users can browse the uploaded files and select the ones they want to transcode. An interactive form is available to guide users through the selection of the file, desired video qualities, audio track, and format.

Upon detection of a new file, the system automatically initiates the transcoding process. Please note that the current system is limited to processing 20 concurrent transcoding processes. Any additional processes beyond this limit will be queued and processed subsequently.

To facilitate management of the transcoding and streaming process, the system provides a user-friendly web-based interface. Additionally, users can access various settings to customize their experience.

In summary, the goal of this project is to deliver an efficient solution for transcoding and streaming video files.

# Getting Started

1. If not already done, [install Docker and Docker Compose (v2.10+)](https://docs.docker.com/engine/install/)
2. Copy and configure your docker compose file `curl https://raw.githubusercontent.com/jameskitt616/orbit/main/docker-compose.yml`
3. Run `docker compose up` (the logs will be displayed in the current shell)
4. Open `http://localhost` or `http://<ip-of-your-server>` in your favorite web browser (make sure to properly configure the firewall of your server)
5. Run `docker compose up -d` to run the Docker containers in detached deamon mode.
6. Don't forget to setup some reverse proxy (just like Caddy or Nginx) with SSL/TLS Certificates

# Features

* Quick and seamless initiation of video transcoding and streaming with just a few clicks.
* Automatic generation of shareable links for easy content access.
* Support for multiple users, allowing simultaneous access and utilization of the system.
* A user interface (UI) designed with a focus on simplicity and ease of use.

**Enjoy!**

## Planned Features

- [ ] Selecting Audio Tracks (currently being worked on)
- [ ] Locking streams behind API keys, allowing for multiple configurable keys per stream.
- [ ] Enabling users to upload video files through a user-friendly web interface (Web-UI).
- [ ] Optimizing the system for mobile view and enabling installation via Browser Web Progressive App (WPA).
- [ ] Offering the ability to configure custom representations, specifying bitrate and quality settings.
- [ ] Providing estimated time of arrival (ETA) for transcoding processes.
- [ ] Enabling the ability to start transcoding from a specific timestamp.

> **_NOTE:_** If there is a significant demand for it, I may consider learning Rust, C++, or another suitable programming language to develop a program or application for controlling the Orbit system from within VR. This would allow users to conveniently manage Streams without needing to leave the VR environment.

# License

Orbit is available under the MIT License.

# Credits

Created by [jameskitt616](https://jameskitt616.one/).
### Special thanks to:
* [Symfony](https://github.com/symfony/symfony)
* [symfony-docker](https://github.com/dunglas/symfony-docker)
* [PHP-FFmpeg-video-streaming](https://github.com/hadronepoch/PHP-FFmpeg-video-streaming)
* [jsTree](https://github.com/vakata/jstree)
* [FFmpeg](https://ffmpeg.org)
