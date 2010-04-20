<%

    strFileURL = Request.QueryString("url")
    Set ip = Request.ServerVariables("REMOTE_ADDR")
    if ip<>"75.127.78.111" and ip<>"127.0.0.1"  Then
        Response.Write("Invalid Caller") 
        Response.End
    End If
 
    Set SWFToImage = CreateObject("SWFToImage.SWFToImageObject")
    SWFToImage.InitLibrary "demo", "demo"	
    SWFToImage.InputSWFFileName = strFileUrl
    SWFToImage.FrameIndex=0
    SWFToImage.ImageOutputType = 2 ' (GIF)
    SWFToImage.Execute
 
    Response.ContentType = "image/gif"
    Response.BinaryWrite(SWFToImage.BinaryImage)
    Set SWFToImage = Nothing


%>