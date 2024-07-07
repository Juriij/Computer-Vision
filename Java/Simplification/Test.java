import org.opencv.core.*;
import org.opencv.highgui.HighGui;
import org.opencv.videoio.VideoCapture;
import org.opencv.imgproc.Imgproc;

import java.nio.ByteBuffer;
import java.util.Arrays;


public class Test {

    // Variables
    private static boolean initialRun = true;
    private static int locObjY, locObjX, locObjMaxDist, locObjHeight, locObjWidth;
    private static Mat locObjImage;

    // Functions
    public static Mat grabFrame(VideoCapture stream) {
        Mat frame = new Mat();
        if (!stream.read(frame)) {
            System.out.println("Error: Could not read frame.");
        }
        return frame;
    }

    public static void analyzeImgOpt(Mat frame) {
        if (initialRun) {
            locObjMaxDist = 40;
            locObjHeight = 60;
            locObjWidth = 60;
            locObjY = (frame.rows() / 2) - (locObjHeight / 2);
            locObjX = (frame.cols() / 2) - (locObjWidth / 2);
            locObjImage = new Mat(frame, new Rect(locObjX, locObjY, locObjWidth, locObjHeight)).clone();
            initialRun = false;
        } else {
            int bestX = locObjX, bestY = locObjY;
            double bestError = Double.MAX_VALUE;

            int frameHeight = frame.rows();
            int frameWidth = frame.cols();

            int startY = Math.max(locObjY - locObjMaxDist, 0);
            int endY = Math.min(locObjY + locObjMaxDist, frameHeight - locObjHeight);
            int startX = Math.max(locObjX - locObjMaxDist, 0);
            int endX = Math.min(locObjX + locObjMaxDist, frameWidth - locObjWidth);

            for (int y = startY; y <= endY; y++) {
                for (int x = startX; x <= endX; x++) {
                    Mat currentPatch = new Mat(frame, new Rect(x, y, locObjWidth, locObjHeight));
                    double error = Core.norm(currentPatch, locObjImage, Core.NORM_L1);

                    if (error < bestError) {
                        bestError = error;
                        bestX = x;
                        bestY = y;
                    }
                }
            }

            locObjX = bestX;
            locObjY = bestY;

            if (bestError < 1000000) {
                locObjImage = new Mat(frame, new Rect(locObjX, locObjY, locObjWidth, locObjHeight)).clone();
            }
        }

        Scalar yellow = new Scalar(0, 255, 255);
        Imgproc.rectangle(frame, new Point(locObjX, locObjY), new Point(locObjX + locObjWidth, locObjY + locObjHeight), yellow, 2);

        String posS = "text";
        int font = Imgproc.FONT_HERSHEY_SIMPLEX;
        double fontScale = 0.5;
        int fontThickness = 1;
        Size textSize = Imgproc.getTextSize(posS, font, fontScale, fontThickness, new int[1]);

        Imgproc.putText(frame, posS, new Point(locObjX + locObjWidth, locObjY + textSize.height), font, fontScale, yellow, fontThickness, Imgproc.LINE_AA);
    }


    public static void main(String[] args) {
        System.loadLibrary(Core.NATIVE_LIBRARY_NAME);

        VideoCapture cap = new VideoCapture(0);
        if (!cap.isOpened()) {
            System.out.println("Error: Could not open video capture.");
            return;
        }


        while (true) {
            Mat frame = grabFrame(cap);

            ByteBuffer buffer = ByteBuffer.allocate(frame.rows() * frame.cols() * (int)frame.elemSize());
            frame.get(0, 0, buffer.array());
            long[] pixels = new long[buffer.array().length];
            Arrays.fill(pixels, 0);
            for (int i = 0; i < buffer.array().length; i++) {
                pixels[i] = buffer.get(i) & 0xFF;
            }

            long startTime = System.currentTimeMillis();
            analyzeImgOpt(frame);
            long endTime = System.currentTimeMillis();


            System.out.printf("%.1f frames per second%n", 1.0 / ((endTime - startTime) / 1000.0));


            HighGui.imshow("Image", frame);

            int key = HighGui.waitKey(1);

            if (key != -1) {
                break;
            }
        }

        cap.release();
        HighGui.destroyAllWindows();
    }
}