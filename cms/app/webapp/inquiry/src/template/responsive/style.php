.inquiry_responsive{
 border-bottom: 1px solid #ccc;
 margin-bottom: 3em;
}
.inquiry_responsive:after{
 content: "";
 height: 0;
 clear: both;
 display: block;
}
.inquiry_responsive dl dt{
 font-size: 0.9rem;
 float: left;
 width: 30%;
 margin: 0;
 border-top: 1px dotted #ccc;
 padding: 0.5em 1em 0;
 margin-top: 0.5em;
}
.inquiry_responsive dl dd{
 float: left;
 width: 65%;
 margin: 0;
 border-top: 1px dotted #ccc;
 padding: 0.5em 0 1em;
 margin-top: 0.5em;
 color: #666;
}
.inquiry_responsive dl dd label{
 color: #333;
}
.inquiry_responsive dl dd p{
 margin-bottom: 0.3em;
}
.inquiry_responsive dl dd p.remarks{
 font-size: 0.9rem;
}

.error_message{
 color: #FF0000;
}

@media screen and (max-width: 839px){
 .inquiry_responsive dl dt{
  width: 90%;
 }
 .inquiry_responsive dl dd{
  width: 100%;
 }
}