      <div class="page-header">
        <h2>Instructions</h2>
      </div>

        <p>In order to capture an HAR file, please follow the following steps:</p>

        <ol class="instructions-list">
          <li>Open <a href="http://google.com/chrome"><strong>Google Chrome</strong></a></li>
          <li>Press F12 to open <strong>Developer Tools</strong> and click on the <strong>Network</strong> tab<br /><br />
            <img class="center-block img-responsive" src="images/instructions-network.jpg" width="75%" height="auto" alt="Google Chrome - Network tab" />
          </li>
          <li>Make sure that the <strong>Record Network Log button</strong> is active (should appear <strong>red</strong>) <br /><br />
            <img class="center-block img-responsive" src="images/instructions-record.jpg" width="75%" height="auto" alt="Google Chrome - Enable recording" />
          </li>
          <li><strong>Browse</strong> to the page containing the player that you want to diagnose, or directly to the player's iframe to examine it in isolation. It's recommended to have 
            the player operating with autoplay enabled to measure the load time accurately. To examine the player on its own, visit: 
            <strong>http://vds.rightster.com/v/VIDEOID?target=iframe&autoplay=1</strong>, replacing <strong>VIDEOID</strong> with the ID of the video being examined.
            <br /><br />
            <img class="center-block img-responsive" src="images/instructions-url.jpg" width="75%" height="auto" alt="Google Chrome - Browse to page" />
          </li>
          <li>If autoplay is not enabled, press play as soon as the player is visible on the page. <strong>Wait</strong> until any adverts have finished playing and the 
            video has started playback. Then <strong>right click</strong> in the Developer Tools below the 'Name/Path' column and click <strong>'Save as HAR with content'</strong>. 
            Enter a filename that includes your name and the website on which the player is present, and save it on your computer. <strong>Wait</strong> for 15 seconds for this to complete.
            <br /><br />
            <img class="center-block img-responsive" src="images/instructions-save.jpg" width="75%" height="auto" alt="Google Chrome - Browse to page" />
          </li>
          <li>To <strong>upload</strong> the file for analysis, either drag the file from where you saved it onto this web page, or use the button at the top of the page to select it 
            from your computer. If you need help in interpreting the results, contact <strong><a href="mailto:laurie@rightster.com">Laurie</a></strong>.</li>
        </ol>

      </div>
