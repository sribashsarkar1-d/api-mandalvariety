package com.mandalvariety.shop

import android.annotation.SuppressLint
import android.app.AlertDialog
import android.content.ActivityNotFoundException
import android.content.Context
import android.content.Intent
import android.graphics.Bitmap
import android.net.ConnectivityManager
import android.net.NetworkCapabilities
import android.net.Uri
import android.os.Bundle
import android.view.View
import android.webkit.CookieManager
import android.webkit.ValueCallback
import android.webkit.WebChromeClient
import android.webkit.WebResourceError
import android.webkit.WebResourceRequest
import android.webkit.WebSettings
import android.webkit.WebView
import android.webkit.WebViewClient
import android.widget.Button
import android.widget.LinearLayout
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity

class MainActivity : AppCompatActivity() {

    private lateinit var webView: WebView
    private lateinit var splashScreen: LinearLayout
    private lateinit var errorScreen: LinearLayout
    private lateinit var btnRetry: Button

    private val SHOP_URL = "https://shop.mandal-variety.com/"
    private val INTERNAL_DOMAIN = "shop.mandal-variety.com"

    private var fileUploadCallback: ValueCallback<Array<Uri>>? = null
    private val FILE_CHOOSER_RESULT_CODE = 1

    @SuppressLint("SetJavaScriptEnabled")
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)

        webView = findViewById(R.id.webView)
        splashScreen = findViewById(R.id.splashScreen)
        errorScreen = findViewById(R.id.errorScreen)
        btnRetry = findViewById(R.id.btnRetry)

        setupWebView()

        btnRetry.setOnClickListener {
            loadWebsite()
        }

        loadWebsite()
    }

    @SuppressLint("SetJavaScriptEnabled")
    private fun setupWebView() {
        val webSettings: WebSettings = webView.settings
        webSettings.javaScriptEnabled = true
        webSettings.domStorageEnabled = true
        webSettings.allowFileAccess = true
        webSettings.setSupportMultipleWindows(false)
        webSettings.javaScriptCanOpenWindowsAutomatically = false
        
        // Cache settings
        webSettings.cacheMode = WebSettings.LOAD_DEFAULT
        
        // Cookie manager setup for sessions
        val cookieManager = CookieManager.getInstance()
        cookieManager.setAcceptCookie(true)
        cookieManager.setAcceptThirdPartyCookies(webView, true)

        webView.webViewClient = object : WebViewClient() {
            override fun onPageStarted(view: WebView?, url: String?, favicon: Bitmap?) {
                super.onPageStarted(view, url, favicon)
                // We keep the splash screen visible until the page finishes loading
            }

            override fun onPageFinished(view: WebView?, url: String?) {
                super.onPageFinished(view, url)
                splashScreen.visibility = View.GONE
                errorScreen.visibility = View.GONE
                webView.visibility = View.VISIBLE
                
                // Ensure cookies are saved
                CookieManager.getInstance().flush()
            }

            override fun onReceivedError(
                view: WebView?,
                request: WebResourceRequest?,
                error: WebResourceError?
            ) {
                super.onReceivedError(view, request, error)
                if (request?.isForMainFrame == true) {
                    showErrorScreen()
                }
            }

            override fun shouldOverrideUrlLoading(view: WebView?, request: WebResourceRequest?): Boolean {
                val url = request?.url?.toString() ?: return false
                val uri = Uri.parse(url)

                // Handle internal links
                if (uri.host?.endsWith(INTERNAL_DOMAIN) == true) {
                    return false // Let WebView handle it
                }

                // Handle external links and intents (tel, mailto, whatsapp)
                return try {
                    val intent = Intent(Intent.ACTION_VIEW, uri)
                    startActivity(intent)
                    true // We handled it externally
                } catch (e: ActivityNotFoundException) {
                    // Fallback if no app can handle the intent
                    if (url.startsWith("intent://")) {
                        val fallbackUrl = try {
                            val parsedIntent = Intent.parseUri(url, Intent.URI_INTENT_SCHEME)
                            val browserFallbackUrl = parsedIntent.getStringExtra("browser_fallback_url")
                            if (browserFallbackUrl != null) {
                                startActivity(Intent(Intent.ACTION_VIEW, Uri.parse(browserFallbackUrl)))
                                true
                            } else {
                                false
                            }
                        } catch (ex: Exception) {
                            false
                        }
                        fallbackUrl
                    } else {
                        Toast.makeText(this@MainActivity, "No application found to handle this action.", Toast.LENGTH_SHORT).show()
                        true
                    }
                }
            }
        }

        webView.webChromeClient = object : WebChromeClient() {
            override fun onShowFileChooser(
                webView: WebView?,
                filePathCallback: ValueCallback<Array<Uri>>?,
                fileChooserParams: FileChooserParams?
            ): Boolean {
                if (fileUploadCallback != null) {
                    fileUploadCallback?.onReceiveValue(null)
                }
                fileUploadCallback = filePathCallback

                val intent = fileChooserParams?.createIntent()
                if (intent != null) {
                    try {
                        startActivityForResult(intent, FILE_CHOOSER_RESULT_CODE)
                    } catch (e: ActivityNotFoundException) {
                        fileUploadCallback = null
                        Toast.makeText(this@MainActivity, "Cannot open file chooser", Toast.LENGTH_SHORT).show()
                        return false
                    }
                    return true
                }
                return false
            }
        }
    }

    private fun loadWebsite() {
        if (isNetworkAvailable()) {
            errorScreen.visibility = View.GONE
            // If splash is not visible, it means we are retrying from error, so show a loading state
            // But we can just rely on the WebView loading
            webView.loadUrl(SHOP_URL)
        } else {
            showErrorScreen()
        }
    }

    private fun showErrorScreen() {
        webView.visibility = View.GONE
        splashScreen.visibility = View.GONE
        errorScreen.visibility = View.VISIBLE
    }

    private fun isNetworkAvailable(): Boolean {
        val connectivityManager = getSystemService(Context.CONNECTIVITY_SERVICE) as ConnectivityManager
        val network = connectivityManager.activeNetwork ?: return false
        val activeNetwork = connectivityManager.getNetworkCapabilities(network) ?: return false
        return when {
            activeNetwork.hasTransport(NetworkCapabilities.TRANSPORT_WIFI) -> true
            activeNetwork.hasTransport(NetworkCapabilities.TRANSPORT_CELLULAR) -> true
            activeNetwork.hasTransport(NetworkCapabilities.TRANSPORT_ETHERNET) -> true
            else -> false
        }
    }

    override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
        if (requestCode == FILE_CHOOSER_RESULT_CODE) {
            if (fileUploadCallback == null) return
            val result = WebChromeClient.FileChooserParams.parseResult(resultCode, data)
            fileUploadCallback?.onReceiveValue(result)
            fileUploadCallback = null
        } else {
            super.onActivityResult(requestCode, resultCode, data)
        }
    }

    override fun onBackPressed() {
        if (webView.canGoBack()) {
            webView.goBack()
        } else {
            AlertDialog.Builder(this)
                .setTitle("Exit Mandal Variety?")
                .setMessage("Are you sure you want to exit?")
                .setPositiveButton("Exit") { _, _ ->
                    super.onBackPressed()
                }
                .setNegativeButton("Cancel", null)
                .show()
        }
    }
}
