kohana-twbs - integrates [Bootstrap](http://getbootstrap.com) with Kohana
=========================================================================

kohana-twbs adds support for twitter bootstrap and font-awesome to a Kohana project, importing the dependencies from
source with composer to ensure they can be easily updated over time.

## Installing the basic library and static assets

Include in your composer.json:

```json
{
	"require": {
		"ingenerator/kohana-twbs" : "dev-master"
	}
}
```

Run composer to install the module and the external dependencies into your project with `composer install`.

You should see this module in your modules directory, and the twitter bootstrap and font-awesome packages under your
vendor directory. The static font and javascript files can't be hosted from there though, so next you need to enable
the module in your bootstrap as usual and then run `minion twbs:publishasssets" to copy the required files from the
vendor packages to your document root. You should update your .gitignore file to avoid checking these copied files into
source control.

## Installing and compiling the less files

For maximum flexibility in customising your site's styles, kohana-twbs defaults to compiling all CSS from the original
LESS sources. A minion task is provided to help with this, which also requires you to install
[recess](http://twitter.github.io/recess/) - the less compiler officially supported by the bootstrap project.

By default, `minion twbs:compile-less` will compile and minify the site.bootstrap.less shipped in the module's
assets/less directory, sending the output to DOCROOT/assets/css/site.bootstrap.css. The shipped file simply combines
bootstrap with the font-awesome icon set.

Once you're ready to start customising styles, drop a site.bootstrap.less in APPATH/assets/less - this will be compiled
instead of the module's version. All the assets/less files will be included in the recess include path, but the way
that recess works is slightly different to the Kohana CFS.

With the options passed by the minion task, recess searches for @import files as follows:

* the directory containing the file with the @import statement
* each of the assets/less folders in the CFS, in sequence
* the font-awesome less folder
* the bootstrap less folder

Therefore, you cannot just drop in for example a carousel.less file and have it replace bootstrap's own. You have two
options:

* define styles that overwrite/extend the bootstrap defaults, and include them after you include bootstrap.
* import the individual bootstrap components from your top-level less file, specifying their paths appropriately, rather
  than just importing the overall bootstrap file.

You can also pass options to the compile-less task to configure other compiler properties including source and
destination paths.

## License

Copyright (c) 2013, inGenerator Ltd
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided
that the following conditions are met:

* Redistributions of source code must retain the above copyright notice, this list of conditions and
  the following disclaimer.
* Redistributions in binary form must reproduce the above copyright notice, this list of conditions
  and the following disclaimer in the documentation and/or other materials provided with the distribution.
* Neither the name of inGenerator Ltd nor the names of its contributors may be used to endorse or
  promote products derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR
IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND
FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS
BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR
BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
