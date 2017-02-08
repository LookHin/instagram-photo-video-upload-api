# Instagram Photo Video Upload API

## Example

```php
include_once("instagram-photo-video-upload-api.class.php");

// Upload Photo
$obj = new InstagramUpload();
$obj->Login("YOUR_IG_USERNAME", "YOUR_IG_PASSWORD");
$obj->UploadPhoto("square-image.jpg", "Test Upload Photo From PHP");

// Upload Video
$obj = new InstagramUpload();
$obj->Login("YOUR_IG_USERNAME", "YOUR_IG_PASSWORD");
$obj->UploadVideo("test-video.mp4", "square-thumb.jpg", "Test Upload Video From PHP");
```
## Rewrite Code From
[https://github.com/mgp25/Instagram-API](https://github.com/mgp25/Instagram-API)

## About Us
Name : Khwanchai Kaewyos (LookHin)  
Email : khwanchai@gmail.com

## Website
[www.unzeen.com](https://www.unzeen.com)  
[Facebook](https://www.facebook.com/LookHin)  



## License (MIT)

Copyright (C) 2017 Khwanchai Kaewyos (LookHin)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
