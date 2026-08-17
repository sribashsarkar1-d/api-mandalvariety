package com.mandalvariety.deliverypartner

import android.annotation.SuppressLint
import android.content.ActivityNotFoundException
import android.content.Intent
import android.graphics.Bitmap
import android.net.Uri
import android.os.Bundle
import android.view.View
import android.webkit.*
import android.widget.Button
import android.widget.ProgressBar
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity

class MainActivity : AppCompatActivity() {

    private lateinit var webView: WebView
    private lateinit var progressBar: ProgressBar
    private lateinit var errorLayout: View
    private lateinit var btnRetry: Button

    private val WEBSITE_URL = "https://delivery-boy.mandal-variety.com/"
    
    private var mUploadMessage: ValueCallback<Uri>? = null
    private var mUploadMessageArray: ValueCallback<Array<Uri>>? = null
    private val FILECHOOSER_RESULTCODE = 1

    @SuppressLint("SetJavaScriptEnabled")
    override fun onCreate(savedInstanceState: Bundle?) {
        // Switch back to normal theme from splash theme before calling super.onCreate
        setTheme(R.style.Theme_DeliveryPartner)
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)

        webView = findViewById(R.id.webView)
        progressBar = findViewById(R.id.progressBar)
        errorLayout = findViewById(R.id.errorLayout)
        btnRetry = findViewById(R.id.btnRetry)

        setupWebView()

        btnRetry.setOnClickListener {
            errorLayout.visibility = View.GONE
            webView.reload()
        }

        if (savedInstanceState == null) {
            webView.loadUrl(WEBSITE_URL)
        }
    }

    @SuppressLint("SetJavaScriptEnabled")
    private fun setupWebView() {
        val webSettings = webView.settings
        webSettings.javaScriptEnabled = true
        webSettings.domStorageEnabled = true
        webSettings.cacheMode = WebSettings.LOAD_DEFAULT
        webSettings.allowFileAccess = true
        webSettings.databaseEnabled = true

        // Accept third party cookies if needed (e.g., for sessions or specific plugins)
        CookieManager.getInstance().setAcceptCookie(true)
        CookieManager.getInstance().setAcceptThirdPartyCookies(webView, true)

        webView.webViewClient = object : WebViewClient() {
            override fun onPageStarted(view: WebView?, url: String?, favicon: Bitmap?) {
                super.onPageStarted(view, url, favicon)
                progressBar.visibility = View.VISIBLE
                errorLayout.visibility = View.GONE
            }

            override fun onPageFinished(view: WebView?, url: String?) {
                super.onPageFinished(view, url)
                progressBar.visibility = View.GONE
            }

            override fun onReceivedError(view: WebView?, request: WebResourceRequest?, error: WebResourceError?) {
                super.onReceivedError(view, request, error)
                if (request?.isForMainFrame == true) {
                    progressBar.visibility = View.GONE
                    errorLayout.visibility = View.VISIBLE
                }
            }

            override fun shouldOverrideUrlLoading(view: WebView?, request: WebResourceRequest?): Boolean {
                val url = request?.url.toString()
                
                // Allow our specific domains to load in the WebView
                if (url.startsWith("https://delivery-boy.mandal-variety.com") || 
                    url.startsWith("http://delivery-boy.mandal-variety.com")) {
                    return false
                }
                
                // Handle external apps like tel:, mailto:, whatsapp:
                try {
                    val intent = Intent(Intent.ACTION_VIEW, Uri.parse(url))
                    startActivity(intent)
                    return true
                } catch (e: ActivityNotFoundException) {
                    // Fallback if no app is found to handle the intent
                    e.printStackTrace()
                }

                // Default behavior for other external URLs
                try {
                    startActivity(Intent(Intent.ACTION_VIEW, Uri.parse(url)))
                } catch (e: Exception) {
                    e.printStackTrace()
                }
                return true
            }
        }

        webView.webChromeClient = object : WebChromeClient() {
            override fun onProgressChanged(view: WebView?, newProgress: Int) {
                if (newProgress < 100) {
                    progressBar.visibility = View.VISIBLE
                } else {
                    progressBar.visibility = View.GONE
                }
            }

            // For Android 5.0+ File Chooser
           override fun onShowFileChooser(
                webView: WebView?,
                filePathCallback: ValueCallback<Array<Uri>>?,
                fileChooserParams: FileChooserParams?
            ): Boolean {
                mUploadMessageArray?.onReceiveValue(null)
                mUploadMessageArray = filePathCallback

                val intent = fileChooserParams?.createIntent()

                if (intent == null) {
                    mUploadMessageArray = null
                    return false
                }

                try {
                    startActivityForResult(intent, FILECHOOSER_RESULTCODE)
                } catch (e: ActivityNotFoundException) {
                    mUploadMessageArray = null
                    return false
                }

                return true
            }
        }
    }

    override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
        if (requestCode == FILECHOOSER_RESULTCODE) {
            if (mUploadMessageArray == null) return
            val result = if (data == null || resultCode != RESULT_OK) null else data.data
            if (result != null) {
                mUploadMessageArray?.onReceiveValue(arrayOf(result))
            } else {
                mUploadMessageArray?.onReceiveValue(null)
            }
            mUploadMessageArray = null
        } else {
            super.onActivityResult(requestCode, resultCode, data)
        }
    }

    override fun onBackPressed() {
        if (errorLayout.visibility == View.VISIBLE) {
            super.onBackPressed()
            return
        }

        if (webView.canGoBack()) {
            webView.goBack()
        } else {
            AlertDialog.Builder(this)
                .setMessage(getString(R.string.exit_app))
                .setPositiveButton(getString(R.string.exit)) { _, _ ->
                    super.onBackPressed()
                }
                .setNegativeButton(getString(R.string.cancel), null)
                .show()
        }
    }

    override fun onSaveInstanceState(outState: Bundle) {
        super.onSaveInstanceState(outState)
        webView.saveState(outState)
    }

    override fun onRestoreInstanceState(savedInstanceState: Bundle) {
        super.onRestoreInstanceState(savedInstanceState)
        webView.restoreState(savedInstanceState)
    }
}
