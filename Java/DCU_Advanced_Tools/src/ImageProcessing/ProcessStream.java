package ImageProcessing;

import java.awt.BasicStroke;
import java.awt.Color;
import java.awt.Graphics;
import java.awt.Transparency;
import java.awt.color.ColorSpace;
import java.awt.event.MouseAdapter;
import java.awt.event.MouseEvent;
import java.awt.event.MouseListener;
import java.awt.image.BufferedImage;





import java.awt.image.ColorModel;
import java.awt.image.ComponentColorModel;
import java.awt.image.ComponentSampleModel;
import java.awt.image.DataBuffer;
import java.awt.image.DataBufferByte;
//import java.awt.image.WritableRaster;



















import java.awt.image.Raster;
import java.awt.image.WritableRaster;

import javax.swing.JFrame;
import javax.swing.JOptionPane;
import javax.swing.WindowConstants;
import javax.swing.ImageIcon;
import javax.swing.JLabel;





















//import org.bytedeco.javacv.CanvasFrame;
import org.bytedeco.javacv.FFmpegFrameGrabber;
import org.bytedeco.javacv.Frame;
import org.bytedeco.javacv.Java2DFrameConverter;
//import org.bytedeco.ffmpeg.global.avutil;




















import com.mysql.cj.jdbc.MysqlDataSource;

import java.nio.ByteBuffer;
import java.sql.Connection;
import java.sql.DatabaseMetaData;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.Statement;
import java.util.Arrays;
import java.util.Calendar;


//import org.opencv.core.Core;
//import org.opencv.core.Mat;
//import org.opencv.videoio.VideoCapture;

public class ProcessStream {
	static JFrame dispJFrame;
	static JLabel dispJLabel;
	static ImageIcon dispImg;
	static FFmpegFrameGrabber mpegGrabber;
	//static Java2DFrameConverter converter;
	static Connection conn;
	static Statement stmt;
	static PreparedStatement pstmt_out, pstmt_in;
	
	static String dcuConfigName = "dcuUserCommConfig";
	static String dcuConfigName_viovalue = "rtVirtualIOvalue";
	
	//user default settings
	static String videoName="camera_64";
	static String videoLink="rtsp://admin:camera64password@10.0.0.64/h264_stream";
	//"jdbc:mysql://127.0.0.1:3306/mysql?useSSL=false";
	static String dburl="jdbc:mysql://127.0.0.1:3306/mysql?useSSL=false&useUnicode=true&useJDBCCompliantTimezoneShift=true&useLegacyDatetimeCode=false&serverTimezone=UTC";
	static String dbadminUsername="root";
	static String dbadminPassw="mysqlpassword";	
	static int rtudp_fcn_out_id = 1200; //server RTUDP output function ID
	static int n_outputs=7;//standard is 1 or 4 or 7 or 23
	static int rtudp_fcn_in_id = 2200; //server RTUDP output function ID
	static int n_inputs=7;//standard is 1 or 4 or 7 or 23
	static int sampling_msec = 200;//expected sampling time for reevaluation
	static double[] rtudp_output;
	static double[] rtudp_input;
	static double[] rtudp_input_previous;//previous samples of rtudp_input
	
	static boolean initialRun=true;
	static boolean stopFlag=false;
	
	static BufferedImage buffImage;
	static Graphics gImage;
	static int imageWidth;
	static int imageHeight;	
	static byte[] pixels;//byte array of actual image		
	static long maxDBconnectionTime=3600000;	
	static String errorMessage="";
	
	static int clickX=-1, clickY=-1;//mouse click position
	static boolean newClick=false;
	
	
	//GLOBALS FOR ANALYZING FUNCTION
	static int origX = -1;//clicked 
	static int origY = -1;//clicked
	static int colorThreshold=10;//number of RGB levels tolerated
	static int upY=-1;//upper limit of found object
	static int downY=-1;//lower limit of found object
	static int leftX=-1;//left limit of found object
	static int rightX=-1;//right limit of found object
	
	static int searchAlgo=2;//type of used analytic algorithm to find object 	
	static int estimSize=60;//expected max. size of searched object
	//locked object
	static int locObjY=-1, locObjX=-1;//actual position of locked object (left top corner), locObjX is the position of the smallest row beginning	
	static int[] locObjX_offset=null;//position of object in each row with respect to locObjX
	static int locObjHeight=-1;//number of rows
	static int locObjWidth=-1;//size of row with maximum value of row size + locObjX_offset[i]	
	static int[] locObjRowWidth=null; //widths for each row of the locked object image which is valid for locked object	
	static byte[] locObjImage;//image array of locked object
	static int locObjMaxDist=-1;//maximum expected distance of locked object from previous position withhin one sample
	static boolean resetPosition=false;
	//------------------------------
	

	public static void main(String[] args) {		
		try{
			
			System.out.print("\n Camera Streaming Initialization ---------------------------------------------\n");
			//Read user inputs -----------------------------------------------------------------------------------------------------------
			//----------------------------------------------------------------------------------------------------------------------------
			if(args.length>8){
				dburl=args[0];
				dbadminUsername=args[1];
				dbadminPassw=args[2];
				videoName=args[3];
				videoLink=args[4];
				try{
					rtudp_fcn_out_id= Integer.valueOf(args[5]).intValue();
					n_outputs = Integer.valueOf(args[6]).intValue();
					rtudp_fcn_in_id= Integer.valueOf(args[7]).intValue();
					n_inputs = Integer.valueOf(args[8]).intValue();
					sampling_msec = Integer.valueOf(args[9]).intValue();
				}
				catch(Exception e){
					//some wrong values
					e.printStackTrace();
					errorMessage+=" Error: Some non-numeric values in RTUDP function ID or number of RTUDP fun. outputs or sampling time. Default values 1200,7,2200,7,200 used instead.";
					rtudp_fcn_out_id = 1200; //server RTUDP output function ID
					n_outputs=7;//standard is 1 or 4 or 7 or 23
					rtudp_fcn_in_id = 2200;
					n_inputs=7;
					sampling_msec = 200;//expected sampling time for reevaluation
				}
			}
			
			
			//Check / adapt user inputs  -------------------------------------------------------------------------------------------------
			//----------------------------------------------------------------------------------------------------------------------------
			if((rtudp_fcn_out_id<1000)||(rtudp_fcn_out_id>65535)){
				errorMessage+=" Error: RTUDP function ID must be from 1000 to 65535. Default 1200 used instead.";
				rtudp_fcn_out_id=1200;
			}
			if((n_outputs<1)||(n_outputs>23)){
				errorMessage+=" Error: Number of outputs for RTUDP function must be between 1 and 23. Default 7 used instead.";
				n_outputs=7;
			}
			if((rtudp_fcn_in_id<1000)||(rtudp_fcn_in_id>65535)){
				errorMessage+=" Error: RTUDP function ID must be from 1000 to 65535. Default 2200 used instead.";
				rtudp_fcn_in_id=2200;
			}
			if((n_inputs<1)||(n_inputs>23)){
				errorMessage+=" Error: Number of outputs for RTUDP function must be between 1 and 23. Default 7 used instead.";
				n_inputs=7;
			}
			if(sampling_msec<10){
				errorMessage+=" Error: Sampling time in msec must be mat least 10msec. Default 200msec used instead.";
				n_outputs=7;
			}
			
			
			
			// Prepare RTUDP MySQL Inputs/Outputs and database connection ----------------------------------------------------------------
			//----------------------------------------------------------------------------------------------------------------------------
			rtudp_output = new double[n_outputs];
			rtudp_input = new double[n_inputs];
			rtudp_input_previous = new double[n_inputs];
			for(int i=0;i<rtudp_output.length;i++){
				rtudp_output[i]=((double)i)*1000;
			}
						
			Thread.currentThread().setPriority(Thread.MAX_PRIORITY);
			
			
            Class.forName("com.mysql.cj.jdbc.Driver").newInstance();//mysql driver
			//create DB connection            
            long connectionTimeStamp = System.currentTimeMillis();            
			conn = DriverManager.getConnection(dburl, dbadminUsername, dbadminPassw);
			stmt = conn.createStatement();
			//RTUDP OUTPUT
			String qry1 = "UPDATE " + dcuConfigName_viovalue + " SET actualValue=? WHERE iocFunId="+rtudp_fcn_out_id+" AND iocFunIOIndex=?";
			pstmt_out = conn.prepareStatement(qry1);
			stmt.executeUpdate("USE " + dcuConfigName);
			//check if database table for specified RTUDP exists
			DatabaseMetaData dbm = conn.getMetaData();
			ResultSet resultSet = dbm.getTables(dcuConfigName, null, dcuConfigName_viovalue, null);
			if(!resultSet.next()){
				errorMessage+= " Error: database table '"+dcuConfigName_viovalue+"' for RTUDP virtual inputs/outputs does not exist!";
			}
			//RTUDP INPUT
			String qry2 = "SELECT  actualValue, iocFunIOIndex FROM " + dcuConfigName_viovalue + " WHERE iocFunId="+rtudp_fcn_in_id;
			pstmt_in = conn.prepareStatement(qry2);
			
			
									
			//Create grabber for receiving stream ----------------------------------------------------------------------------------------
			//----------------------------------------------------------------------------------------------------------------------------
			
			//https://ffmpeg.org/ffmpeg-codecs.html		
			//disable warning from ffmpeg player
			org.bytedeco.ffmpeg.global.avutil.av_log_set_level(org.bytedeco.ffmpeg.global.avutil.AV_LOG_PANIC);
			mpegGrabber = new FFmpegFrameGrabber(videoLink);
			mpegGrabber.setOption("fflags", "nobuffer");
			mpegGrabber.setOption("flags", "low_delay");
			//mpegGrabber.setOption("framedrop", "1");
			//mpegGrabber.setOption("analyzeduration", "0");
			//mpegGrabber.setOption("probesize", "32");
			//mpegGrabber.setOption("sync", "ext");
			//mpegGrabber.setOption("vf", "setpts=0");
			//mpegGrabber.setOption("fflags", "flush_packets");
			mpegGrabber.setOption("preset", "ultrafast");
			//mpegGrabber.setVideoOption("look_ahead", "0");
			//mpegGrabber.setVideoOption("max_frame_delay", "1");			
			//mpegGrabber.setImageWidth(1280);
            //pegGrabber.setImageHeight(720);
            //mpegGrabber.setPixelFormat(12);
			mpegGrabber.setVideoOption("threads", "1");//to accelerate decoding to max.
			mpegGrabber.start();
			
			//convert first captured frame
			Frame mpegFrame = mpegGrabber.grabImage();			
			long ts1=System.currentTimeMillis();
			//converter = new Java2DFrameConverter();
			//BufferedImage buffImage = converter.convert(mpegFrame);
			//converter.close();
			//pixels in byte arrray
			//pixels = ((DataBufferByte) buffImage.getRaster().getDataBuffer()).getData();//3bytes for each pixel if no alpha canal
			convertFrame(mpegFrame);
			
			ts1=System.currentTimeMillis()-ts1;
			imageWidth = buffImage.getWidth();
			imageHeight = buffImage.getHeight();
			
			gImage = buffImage.getGraphics();//object for displaying texts and elements inside image
			
			
			
			//Create GUI and display image -----------------------------------------------------------------------------------------------
			//----------------------------------------------------------------------------------------------------------------------------
			dispJFrame = new JFrame();
			dispImg = new ImageIcon(buffImage);
			dispJLabel = new JLabel(dispImg);						
			
			dispJFrame.getContentPane().add(dispJLabel);
	        dispJFrame.setDefaultCloseOperation(WindowConstants.EXIT_ON_CLOSE);
	        dispJFrame.setSize(buffImage.getWidth()+10, buffImage.getHeight() + 40);
	        dispJFrame.setTitle("DCU Advanced Image Processing - RTUDP Output Function "+rtudp_fcn_out_id +", RTUDP Input Function "+rtudp_fcn_in_id+" - Updated outputs 0 to "+(n_outputs-1)+". Streaming "+videoName);
	        dispJFrame.setVisible(true);
	        dispJFrame.setResizable(false);
	        dispJFrame.setDefaultCloseOperation(WindowConstants.DO_NOTHING_ON_CLOSE);
	        dispJFrame.addWindowListener(new java.awt.event.WindowAdapter() {
	            
	            public void windowClosing(java.awt.event.WindowEvent windowEvent) {	            	
	                if (JOptionPane.showConfirmDialog(dispJFrame, 
	                    "Are you sure you want to exit?", "Close Window?", 
	                    JOptionPane.YES_NO_OPTION,
	                    JOptionPane.QUESTION_MESSAGE) == JOptionPane.YES_OPTION){
	                	try{
	                		mpegGrabber.close();
	                	}
	                	catch(Exception ee){
	                		ee.printStackTrace();
	                	}
	                    stopFlag=true;
	                }
	                
	            }
	        });
	        
	        dispJFrame.addMouseListener(new MouseAdapter() {
	            public void mouseClicked(MouseEvent e) {
	                clickX = e.getX()-4;
	                clickY = e.getY()-30;
	                newClick=true;
	            }
	        });
	        	        
				        
	       
	        
	        
	        //Start loop for streaming video ---------------------------------------------------------------------------------------------
			//----------------------------------------------------------------------------------------------------------------------------
	       			
	        System.out.print("\n Process Streaming Starting Loop ------------------------------------------------\n");
			long tstamp1, tstamp2, tstamp3, tstamp4, dt1, dt2,dt3;
			long[] execTimeGrab = new long[50];
			long[] execTimeConv = new long[50];
			long[] execTimeEval = new long[50];
			long min1=-1;
			long min2=-1;
			long min3=-1; 
			long max1=1000000000;
			long max2=1000000000;
			long max3=1000000000;
			long avg1 =-1;
			long avg2 =-1;
			long avg3 =-1;
			int c1=0;
			String text_1 = "";
			String text_2 = "";
			String loop_error="";
			ResultSet resSet;
			
			while(!stopFlag){
				
				//Grabbing image --------------------------------------------------------------------------------
				//-----------------------------------------------------------------------------------------------
				tstamp1 =System.currentTimeMillis();
				mpegFrame = mpegGrabber.grabImage();
				//converter = new Java2DFrameConverter();
				//buffImage = converter.convert(mpegFrame);	//conversion
				//converter.close();
				//pixels = ((DataBufferByte) buffImage.getRaster().getDataBuffer()).getData();//3bytes for each pixel if no alpha canal
				convertFrame(mpegFrame);
				
				
				
				//Check DB connection time - restart needed every 1 hour-----------------------------------------
				//-----------------------------------------------------------------------------------------------
				tstamp2 =System.currentTimeMillis();
				
				if((System.currentTimeMillis()-connectionTimeStamp)>maxDBconnectionTime){
					if(conn!=null){
						conn.close();							
					}
					if(stmt!=null){
						stmt.close();
					}						
					//opening DB connection
					conn = DriverManager.getConnection(dburl, dbadminUsername, dbadminPassw);						
					//create statement            
					stmt = conn.createStatement();
					stmt.executeUpdate("USE " + dcuConfigName);
					connectionTimeStamp=System.currentTimeMillis();					 
					pstmt_out = conn.prepareStatement(qry1);
					pstmt_in = conn.prepareStatement(qry2);
				}	
				if(stmt.isClosed()){
					//opening DB connection
					conn = DriverManager.getConnection(dburl, dbadminUsername, dbadminPassw);							
					//create statement            
					stmt = conn.createStatement();
					stmt.executeUpdate("USE " + dcuConfigName);
					connectionTimeStamp=System.currentTimeMillis();					 
					pstmt_out = conn.prepareStatement(qry1);
					pstmt_in = conn.prepareStatement(qry2);
				}								
				
				
				
				//Analyze Image - make image processing and update values in rtudp_output[i], i=0 .. 6 ----------
				//-----------------------------------------------------------------------------------------------
				AnalyzeImage(buffImage, rtudp_output);

				
				
				//Place modified RTUDP Output Values to database for sending to DCU -----------------------------
				//-----------------------------------------------------------------------------------------------
				int nOK=0;
			    for(int k=0;k<n_outputs;k++){
			    	pstmt_out.setString(1, String.valueOf(rtudp_output[k]));//value										
					pstmt_out.setInt(2, k);//output index
					int stat = pstmt_out.executeUpdate();		
					nOK+=stat;
			    }
			    if(nOK<n_outputs){
			    	loop_error+=" Error updating RTUDP outputs, only "+nOK+" updated out of "+n_outputs+"!";
			    }
			    
			    
			    			    
			    //Read RTUDP Input Values ---------------------------------------------------------------------
			    //-----------------------------------------------------------------------------------------------
			    nOK=0;
			    resSet = pstmt_in.executeQuery();
			    while(resSet.next()){
			    	int idx=resSet.getInt("iocFunIOIndex");
			    	rtudp_input_previous[idx]=rtudp_input[idx];//Record previous inputs			    	
			    	rtudp_input[idx] = resSet.getDouble("actualValue");
			    	nOK++;
			    }
			    if(nOK<n_inputs){
			    	loop_error+=" Error reading RTUDP inputs, only "+nOK+" read out of "+n_inputs+"!";
			    }
			    
			   
			    //Displaying image ----------------------------------------------------------------------------
			    //---------------------------------------------------------------------------------------------
				tstamp3 =System.currentTimeMillis();	
				
				gImage = buffImage.getGraphics();
				gImage.setFont(gImage.getFont().deriveFont(12f));
			    gImage.drawString(text_1, 10, 670);
			    gImage.drawString(text_2, 10, 690);		
			    String errorMessage1 = loop_error + errorMessage;
			    if(!errorMessage1.isEmpty()){			    	
			    	if(errorMessage1.length()<=100){
			    		gImage.drawString(errorMessage1, 20, 20);
			    	}
			    	else{
			    		int strS=0;
			    		int strE=100;
			    		int count=0;
			    		while(strE<errorMessage1.length()){			    			
			    			String s1 = errorMessage1.substring(strS,strE);
			    			gImage.drawString(s1, 10, 10+count*20);
			    			strS=strE;
			    			if(errorMessage1.length()>(strE+100)) strE+=100; else strE=errorMessage1.length();
			    			count++;
			    		}
			    		
			    	}
			    }
			    gImage.dispose();			    
				dispImg.setImage(buffImage);
				dispJLabel.invalidate();
				dispJLabel.revalidate();
				dispJLabel.repaint();
				
				
				//Evaluating execution time ---------------------------------------------------------------------
				//-----------------------------------------------------------------------------------------------
				tstamp4 =System.currentTimeMillis();
				dt1=tstamp2-tstamp1;//grabbing
				dt2=tstamp4-tstamp3;//displaying
				dt3=tstamp3-tstamp2;//evaluation
				execTimeGrab[c1]=dt1;
				execTimeConv[c1]=dt2;
				execTimeEval[c1]=dt3;				
				c1++;	
				
				//check and compare excution times 
				dt1 = sampling_msec - (dt1+dt2+dt3);				
				if(c1==execTimeGrab.length){
					//compute min max avg
					min1 = execTimeGrab[0];
					max1 = execTimeGrab[0];
					avg1 = execTimeGrab[0];
					min2 = execTimeConv[0];
					max2 = execTimeConv[0];
					avg2 = execTimeConv[0];
					min3 = execTimeEval[0];
					max3 = execTimeEval[0];
					avg3 = execTimeEval[0];
					for(int i=1;i<execTimeGrab.length;i++){
						if(min1> execTimeGrab[i]) min1= execTimeGrab[i];
						if(min2> execTimeConv[i]) min2= execTimeConv[i];
						if(min3> execTimeEval[i]) min3= execTimeEval[i];
						if(max1< execTimeGrab[i]) max1= execTimeGrab[i];
						if(max2< execTimeConv[i]) max2= execTimeConv[i];
						if(max3< execTimeEval[i]) max3= execTimeEval[i];
						avg1+= execTimeGrab[i];
						avg2+= execTimeConv[i];
						avg3+= execTimeEval[i];
					}
					avg1=avg1/execTimeGrab.length;
					avg2=avg2/execTimeGrab.length;
					avg3=avg3/execTimeGrab.length;
					
					c1=0;
					
					text_1 = "Eval Img \t\t"+avg3+"ms"+ " \t(max)" +max3+"ms";
					text_2 = "Total Exec \t"+(avg1+avg2+avg3)+"ms"+ " \t(max)" +(max1+max2+max3)+"ms";
					//System.out.print("\n ----------------------------------------------- \n");
					//System.out.print("\n Time [msec] \tmin \tavg \tmax \n");
					//System.out.print("\n Grab Img \t"+min1+" \t"+avg1+ " \t" +max1+ " \n");
					//System.out.print("\n Convert Img \t"+min2+" \t"+avg2+ " \t" +max2+ " \n");
					//System.out.print("\n Eval Img \t"+min3+" \t"+avg3+ " \t" +max3+ " \n");
					//System.out.print(" ----------------------------------------");
					//System.out.print("\n Total \t\t"+(min1+min2+min3)+" \t"+(avg1+avg2+avg3)+ " \t" +(max1+max2+max3)+ " \n");					
					//System.out.print("\n ----------------------------------------------- \n");
					
				}
				loop_error="";			
				initialRun=false;
				if(dt1>60){
					Thread.sleep(30);
				}
			
			}//while loop
			
			
		}
		catch(Exception mainE){			
			mainE.printStackTrace();
		}
		
		System.exit(0);
	}
	
	
	
	private static void convertFrame(Frame frame){

		ByteBuffer buffer = (ByteBuffer) frame.image[0].position(0);

        if(pixels == null)
            pixels = new byte[buffer.limit()];

        buffer.get(pixels);

        ColorSpace cs = ColorSpace.getInstance(ColorSpace.CS_sRGB);

        ColorModel cm = new ComponentColorModel(cs, false,false, Transparency.OPAQUE, DataBuffer.TYPE_BYTE);
        WritableRaster wr = Raster.createWritableRaster(new ComponentSampleModel(DataBuffer.TYPE_BYTE, frame.imageWidth, frame.imageHeight, frame.imageChannels, frame.imageStride, new int[] {2, 1, 0}), null);
        byte[] bufferPixels = ((DataBufferByte) wr.getDataBuffer()).getData();

        //System.arraycopy(pixels, 0, bufferPixels, 0, pixels.length);
        for(int i=0;i<pixels.length;i++){
        	bufferPixels[i]=pixels[i];
        }
        
        if(buffImage==null){
        	buffImage = new BufferedImage(cm, wr, false, null);
        }
        else{
        	gImage.dispose();
        	buffImage.flush();
        	buffImage = new BufferedImage(cm, wr, false, null);
        }
        
        return; 
	}
	
	
	
	/**
	 * Analysis of the image and sending results to DCU (Dynamic Control Unit)
	 * @param buffImage - actually captured image frame
	 * @param rtudp_output - array of values that are sent to DCU
	 */
	private static void AnalyzeImage(BufferedImage buffImage, double[] rtudp_output){
		//clickX, clickY, newClick global variables indicates if there was a new mouse click and its X,Y position in the image
		
		//TODO Code
		
		
		//EXAMPLES ----------------------------------------------------------------------------------------------
		//-------------------------------------------------------------------------------------------------------
		
		//EXAMPLE FILLING RTUDP OUTPUTS -----------------------------------------------------
		//-----------------------------------------------------------------------------------
		for(int i=0;i<rtudp_output.length;i++){
			rtudp_output[i]= rtudp_output[i] + 0.1;
		}
		
		
		//EXAMPLE X,Y POSITION EVALUATED FROM IMAGE -----------------------------------------
		//-----------------------------------------------------------------------------------
		if(rtudp_input[0]>0){//evaluation enabled
			if((rtudp_input_previous[0]<=0)||(initialRun)||(resetPosition)){//re-set initial position
				//reset  X Y actual position and object form and size (square 100x100)
				locObjMaxDist=40;//px
				locObjHeight=60;//px
				locObjWidth=60;//largest row size+offset value				
				locObjRowWidth = new int[locObjHeight];	
				locObjX_offset = new int[locObjHeight];
				if(!resetPosition){//get center
					locObjY=(imageHeight/2)-(locObjHeight/2);
					locObjX=(imageWidth/2)-(locObjWidth/2);
				}
				else{
					resetPosition=false;
				}
							
				for(int i=0;i<locObjHeight;i++){
					locObjX_offset[i]=0;
					locObjRowWidth[i]=1;					
				}
				locObjRowWidth[0]=locObjWidth;
				locObjRowWidth[30]=locObjWidth;
				locObjRowWidth[59]=locObjWidth;
				
				
				//lock and copy central object
				locObjImage = new byte[locObjHeight*locObjWidth*3];	
				int locObjIdx = 0;				
				for(int y=0;y<locObjHeight;y++){//for all rows
					locObjIdx+=locObjX_offset[y]*3;//jump unused pixels at the beginning of the row
					for(int x=locObjX_offset[y];x<(locObjX_offset[y]+locObjRowWidth[y]);x++){//one row						
						for(int px=0;px<3;px++){//3 RGB values							
							int imgIdx = (locObjY+y)*imageWidth*3 + (locObjX+x)*3 + px;							
							locObjImage[locObjIdx] = pixels[imgIdx];
							locObjIdx++;
						}
					}
					locObjIdx+=(locObjWidth-locObjX_offset[y]-locObjRowWidth[y])*3;//jump unused pixels at the end
				}		
				//initial position that is considered to be x=0, y=0
				origX=locObjX;
				origY=locObjY;
				
			}
			else{
				//search for new position of locked object in the image and get new copy of the object
				long tstamp_A=System.currentTimeMillis();
				int bestX=0;
				int bestY=0;
				long bestError=1000000000;
				int startY=locObjY-locObjMaxDist;
				if(startY<1) startY=1;
				int endY = locObjY+locObjMaxDist;
				if(endY>imageHeight) endY=imageHeight;
				for(int acty=startY;acty<=endY;acty++){//for all possible rows
					int startX = locObjX-locObjMaxDist;
					if(startX<1) startX=1;
					int endX = locObjX+locObjMaxDist;
					if(endX>imageWidth) endX=imageWidth;
					for(int actx=startX;actx<=endX;actx++){//for all possible columns
						//evaluate error of SUM abs(image - locked object)
						long actErr=0;
						int locObjIdx = 0;
						for(int y=0;y<locObjHeight;y++){//for all rows
							locObjIdx+=locObjX_offset[y]*3;//jump unused pixels at the beginning of the row
							for(int x=locObjX_offset[y];x<(locObjX_offset[y]+locObjRowWidth[y]);x++){//one row
								int imgIdx = (acty+y)*imageWidth*3 + (actx+x)*3;										
								if(imgIdx>(pixels.length-3)){//border
									actErr+=1000000;
								}
								else{
									actErr += Math.abs(locObjImage[locObjIdx]+locObjImage[locObjIdx+1]+locObjImage[locObjIdx+2] - pixels[imgIdx]-pixels[imgIdx+1]-pixels[imgIdx+2]);
								}
								locObjIdx+=3;
							}							
							locObjIdx+=(locObjWidth-locObjX_offset[y]-locObjRowWidth[y])*3;//jump unused pixels at the end
						}
						//check if new better than previous
						if(bestError>actErr){
							bestError=actErr;
							bestX = actx;
							bestY = acty;
						}
						//gIm.drawRect(actx, acty, locObjWidth,locObjHeight);					    
					}//for actx
				}//for acty
				
				locObjX=bestX;
				locObjY=bestY;				
				if(bestError>0){//copy new object image
					int locObjIdx = 0;				
					for(int y=0;y<locObjHeight;y++){//for all rows
						locObjIdx+=locObjX_offset[y]*3;//jump unused pixels at the beginning of the row
						for(int x=locObjX_offset[y];x<(locObjX_offset[y]+locObjRowWidth[y]);x++){//one row						
							for(int px=0;px<3;px++){//3 RGB values							
								int imgIdx = (locObjY+y)*imageWidth*3 + (locObjX+x)*3 + px;							
								locObjImage[locObjIdx] = pixels[imgIdx];
								locObjIdx++;
							}
						}
						locObjIdx+=(locObjWidth-locObjX_offset[y]-locObjRowWidth[y])*3;//jump unused pixels at the end
					}					
				}
				//update RTUDP outputs
				rtudp_output[0]= locObjX - origX;
				rtudp_output[1]= locObjY - origY;
				tstamp_A = System.currentTimeMillis()-tstamp_A;
				 
			}
			//draw delimiters 	
			Graphics gImage = buffImage.getGraphics();			
			gImage.setColor(Color.YELLOW);
			gImage.drawRoundRect(locObjX, locObjY, locObjWidth,locObjHeight, 5, 5);
			gImage.setFont(gImage.getFont().deriveFont(10f));
			String posS= "x="+String.valueOf(rtudp_output[0])+", y=" + String.valueOf(rtudp_output[1]);
		    gImage.drawString(posS, locObjX+locObjWidth, locObjY);
		    gImage.dispose();		
		}//if enabled
		
		
		
		//EXAMPLE FIND OBJECT CLICKED BY MOUSE ----------------------------------------------
		//-----------------------------------------------------------------------------------
		if(newClick){
			//get selected pixel
			origX = clickX;
			origY = clickY;
			
			
			//search around for similar color			
			upY=origY;
			downY=origY;
			leftX=origX;
			rightX=origX;			
			
			if(searchAlgo==0){
				searchMaxColorDeviation(buffImage, origX,origY);
			}
			else if(searchAlgo==1){
				searchEdges(buffImage, origX, origY);
			}
			else if(searchAlgo==2){//lock new position X,Y positon localization
				locObjY=origY;
				locObjX=origX;
				resetPosition=true;
			}
			
			newClick=false;//new click has been processed
		}
		//draw delimiters if some
		if(origX>0){
			Graphics gImage = buffImage.getGraphics();
			gImage.setFont(gImage.getFont().deriveFont(16f));
			gImage.drawRoundRect(leftX, upY, rightX-leftX, downY-upY, 1, 1);
		    gImage.dispose();
		}
		
			
			
		
		
		//EXAMPLE TIME AND DATE DISPLAY -----------------------------------------------------
		//-----------------------------------------------------------------------------------
		Calendar cld= Calendar.getInstance();
		cld.setTimeInMillis(System.currentTimeMillis());		
		String timstr =  String.valueOf(cld.get(Calendar.DAY_OF_MONTH))+"."+
				String.valueOf(cld.get(Calendar.MONTH)+1) + "." + String.valueOf(cld.get(Calendar.YEAR)) + " - " +  
				String.valueOf(cld.get(Calendar.HOUR_OF_DAY)) + "h" + String.valueOf(cld.get(Calendar.MINUTE)) + ":" +
				String.valueOf(cld.get(Calendar.SECOND)) +"."+ String.valueOf(cld.get(Calendar.MILLISECOND));
		Graphics gImage = buffImage.getGraphics();
		gImage.setFont(gImage.getFont().deriveFont(10f));
	    gImage.drawString(timstr, 1100, 10);		    
	    gImage.dispose();
		
	}
	

	
	
	/**
	 * Search for an object borders based on edge detection
	 * @param buffImage - processed image
	 * @param origX - position x of supposed center of the object
	 * @param origY - position y of supposed center of the object
	 */
	static void searchEdges(BufferedImage buffImage, int origX, int origY){
		//get color of origin
		int origCol = buffImage.getRGB(origX, origY); //blue rgb & 0xFF, green (rgb >> 8) & 0xF, red (rgb >> 16) & 0xFF
		int origR = (origCol >> 16) & 0xFF;
		int origG = (origCol >> 8) & 0xFF;
		int origB = origCol  & 0xFF;
		//going up
		if(origY>1){
			int maxDeviation=-1;
			for(int idx=1;idx<estimSize;idx++){
				int actY=origY-idx;
				if(actY>0){
					int actCol = buffImage.getRGB(origX, actY);
					int actR = (actCol >> 16) & 0xFF;
					int actG = (actCol >> 8) & 0xFF;
					int actB = actCol  & 0xFF;
					int prevCol = buffImage.getRGB(origX, actY+1);
					int prevR = (prevCol >> 16) & 0xFF;
					int prevG = (prevCol >> 8) & 0xFF;
					int prevB = prevCol  & 0xFF;					
					int devR = Math.abs(actR-prevR);
					int devG = Math.abs(actG-prevG);
					int devB = Math.abs(actB-prevB);
					if(maxDeviation < devR) {
						maxDeviation=devR;
						upY=actY;
					}
					if(maxDeviation < devG) {
						maxDeviation=devG;
						upY=actY;
					}
					if(maxDeviation < devB) {
						maxDeviation=devB;
						upY=actY;
					}
				}
				else{
					break;
				}
			}//for
		}//if
		
		//going down
		if(origY<imageHeight){
			int maxDeviation=-1;
			for(int idx=1;idx<estimSize;idx++){
				int actY=origY+idx;
				if(actY<imageHeight){
					int actCol = buffImage.getRGB(origX, actY);
					int actR = (actCol >> 16) & 0xFF;
					int actG = (actCol >> 8) & 0xFF;
					int actB = actCol  & 0xFF;
					int prevCol = buffImage.getRGB(origX, actY-1);
					int prevR = (prevCol >> 16) & 0xFF;
					int prevG = (prevCol >> 8) & 0xFF;
					int prevB = prevCol  & 0xFF;					
					int devR = Math.abs(actR-prevR);
					int devG = Math.abs(actG-prevG);
					int devB = Math.abs(actB-prevB);
					if(maxDeviation < devR) {
						maxDeviation=devR;
						downY=actY;
					}
					if(maxDeviation < devG) {
						maxDeviation=devG;
						downY=actY;
					}
					if(maxDeviation < devB) {
						maxDeviation=devB;
						downY=actY;
					}
				}
				else{
					break;
				}
			}//for
		}//if
		
		//going right
		if(origX<imageWidth){
			int maxDeviation=-1;
			for(int idx=1;idx<estimSize;idx++){
				int actX=origX+idx;
				if(actX<imageWidth){
					int actCol = buffImage.getRGB(actX, origY);
					int actR = (actCol >> 16) & 0xFF;
					int actG = (actCol >> 8) & 0xFF;
					int actB = actCol  & 0xFF;
					int prevCol = buffImage.getRGB(actX-1, origY);
					int prevR = (prevCol >> 16) & 0xFF;
					int prevG = (prevCol >> 8) & 0xFF;
					int prevB = prevCol  & 0xFF;					
					int devR = Math.abs(actR-prevR);
					int devG = Math.abs(actG-prevG);
					int devB = Math.abs(actB-prevB);
					if(maxDeviation < devR) {
						maxDeviation=devR;
						rightX=actX;
					}
					if(maxDeviation < devG) {
						maxDeviation=devG;
						rightX=actX;
					}
					if(maxDeviation < devB) {
						maxDeviation=devB;
						rightX=actX;
					}
				}
				else{
					break;
				}
			}//for
		}//if
		
		//going left
		if(origX>0){
			int maxDeviation=-1;
			for(int idx=1;idx<estimSize;idx++){
				int actX=origX-idx;
				if(actX>0){
					int actCol = buffImage.getRGB(actX, origY);
					int actR = (actCol >> 16) & 0xFF;
					int actG = (actCol >> 8) & 0xFF;
					int actB = actCol  & 0xFF;
					int prevCol = buffImage.getRGB(actX+1, origY);
					int prevR = (prevCol >> 16) & 0xFF;
					int prevG = (prevCol >> 8) & 0xFF;
					int prevB = prevCol  & 0xFF;					
					int devR = Math.abs(actR-prevR);
					int devG = Math.abs(actG-prevG);
					int devB = Math.abs(actB-prevB);
					if(maxDeviation < devR) {
						maxDeviation=devR;
						leftX=actX;
					}
					if(maxDeviation < devG) {
						maxDeviation=devG;
						leftX=actX;
					}
					if(maxDeviation < devB) {
						maxDeviation=devB;
						leftX=actX;
					}
				}
				else{
					break;
				}
			}//for
		}//if
		
		
	}//searchEdges
	
	
	/**
	 * Search for an object borders based on color difference
	 * @param buffImage - processed image
	 * @param origX - position x of supposed center of the object
	 * @param origY - position y of supposed center of the object
	 */
	static void searchMaxColorDeviation(BufferedImage buffImage, int origX, int origY){
		//get color of origin
		int origCol = buffImage.getRGB(origX, origY); //blue rgb & 0xFF, green (rgb >> 8) & 0xF, red (rgb >> 16) & 0xFF
		int origR = (origCol >> 16) & 0xFF;
		int origG = (origCol >> 8) & 0xFF;
		int origB = origCol  & 0xFF;
		//going up
		if(origY>1){
			int deviation=0;				
			while(deviation<colorThreshold){
				upY--;
				if(upY>0){
					int actCol = buffImage.getRGB(origX, upY);
					int actR = (actCol >> 16) & 0xFF;
					int actG = (actCol >> 8) & 0xFF;
					int actB = actCol  & 0xFF;
					deviation = Math.abs(actR-origR);
					int devG = Math.abs(actG-origG);
					int devB = Math.abs(actB-origB);
					if(deviation < devG) deviation=devG;
					if(deviation < devB) deviation=devB;
				}
				else{
					upY=0;
					break;
				}
			}
		}
		//going down
		if(origY<imageHeight){
			int deviation=0;				
			while(deviation<colorThreshold){
				downY++;
				if(downY<imageHeight){
					int actCol = buffImage.getRGB(origX, downY);						
					int actR = (actCol >> 16) & 0xFF;
					int actG = (actCol >> 8) & 0xFF;
					int actB = actCol  & 0xFF;
					deviation = Math.abs(actR-origR);
					int devG = Math.abs(actG-origG);
					int devB = Math.abs(actB-origB);
					if(deviation < devG) deviation=devG;
					if(deviation < devB) deviation=devB;
				}
				else{
					downY=imageHeight;
					break;
				}
			}
		}
		//going left
		if(origX>1){
			int deviation=0;				
			while(deviation<colorThreshold){
				leftX--;
				if(leftX>0){
					int actCol = buffImage.getRGB(leftX, origY);
					int actR = (actCol >> 16) & 0xFF;
					int actG = (actCol >> 8) & 0xFF;
					int actB = actCol  & 0xFF;
					deviation = Math.abs(actR-origR);
					int devG = Math.abs(actG-origG);
					int devB = Math.abs(actB-origB);
					if(deviation < devG) deviation=devG;
					if(deviation < devB) deviation=devB;
				}
				else{
					leftX=0;
					break;
				}
			}
		}
		//going right
		if(origX<imageWidth){
			int deviation=0;				
			while(deviation<colorThreshold){
				rightX++;
				if(rightX<imageWidth){
					int actCol = buffImage.getRGB(rightX, origY);
					int actR = (actCol >> 16) & 0xFF;
					int actG = (actCol >> 8) & 0xFF;
					int actB = actCol  & 0xFF;
					deviation = Math.abs(actR-origR);
					int devG = Math.abs(actG-origG);
					int devB = Math.abs(actB-origB);
					if(deviation < devG) deviation=devG;
					if(deviation < devB) deviation=devB;
				}
				else{
					rightX=imageWidth;
					break;
				}
			}
		}
	}
		

	   

}
