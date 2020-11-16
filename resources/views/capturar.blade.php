<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

    <!-- Styles -->
    <style>
        html, body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Nunito', sans-serif;
            font-weight: 200;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 100vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .top-right {
            position: absolute;
            right: 10px;
            top: 18px;
        }

        .content {
            text-align: center;
        }

        .title {
            font-size: 84px;
        }

        .links > a {
            color: #636b6f;
            padding: 0 25px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }

        .m-b-md {
            margin-bottom: 30px;
        }
    </style>
    <script async src="/js/opencv.js" onload="openCvReady();"></script>
    <script src="/js/utils.js"></script>
</head>
<body>
<div class="flex-center position-ref full-height">
    @if (Route::has('login'))
        <div class="top-right links">
            @auth
                <a href="{{ url('/home') }}">Home</a>
            @else
                <a href="{{ route('login') }}">Login</a>

                @if (Route::has('register'))
                    <a href="{{ route('register') }}">Register</a>
                @endif
            @endauth
        </div>
    @endif

    <div class="content">
        <video id="cam_input" height="480" width="640"></video>
        <canvas id="canvas_output"></canvas>
    </div>
</div>
</body>
<script type="text/JavaScript">
    function openCvReady() {
        cv['onRuntimeInitialized']=()=>{
            let video = document.getElementById("cam_input"); // video is the id of video tag
            navigator.mediaDevices.getUserMedia({ video: true, audio: false })
                .then(function(stream) {
                    video.srcObject = stream;
                    video.play();
                })
                .catch(function(err) {
                    console.log("An error occurred! " + err);
                });
            let src = new cv.Mat(video.height, video.width, cv.CV_8UC4);
            let dst = new cv.Mat(video.height, video.width, cv.CV_8UC1);
            let gray = new cv.Mat();
            let cap = new cv.VideoCapture(cam_input);
            let faces = new cv.RectVector();
            let classifier = new cv.CascadeClassifier();
            let utils = new Utils('errorMessage');
            let faceCascadeFile = '/haarcascade_frontalface_default.xml'; // path to xml
            utils.createFileFromUrl(faceCascadeFile, faceCascadeFile, () => {
                classifier.load(faceCascadeFile); // in the callback, load the cascade from file
            });
            const FPS = 24;
            function processVideo() {
                let begin = Date.now();
                cap.read(src);
                src.copyTo(dst);
                cv.cvtColor(dst, gray, cv.COLOR_RGBA2GRAY, 0);
                try{
                    classifier.detectMultiScale(gray, faces, 1.1, 3, 0);
                    console.log(faces.size());
                }catch(err){
                    console.log(err);
                }
                for (let i = 0; i < faces.size(); ++i) {
                    let face = faces.get(i);
                    let point1 = new cv.Point(face.x, face.y);
                    let point2 = new cv.Point(face.x + face.width, face.y + face.height);
                    cv.rectangle(dst, point1, point2, [255, 0, 0, 255]);
                }
                cv.imshow("canvas_output", dst);
                // schedule next one.
                let delay = 1000/FPS - (Date.now() - begin);
                setTimeout(processVideo, delay);
            }
            setTimeout(processVideo, 0);
        };
    }
</script>
</html>
