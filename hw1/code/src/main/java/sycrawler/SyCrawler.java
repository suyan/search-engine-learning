package sycrawler;

import com.google.common.io.Files;
import edu.uci.ics.crawler4j.crawler.Page;
import edu.uci.ics.crawler4j.crawler.WebCrawler;
import edu.uci.ics.crawler4j.parser.BinaryParseData;
import edu.uci.ics.crawler4j.parser.HtmlParseData;
import edu.uci.ics.crawler4j.url.WebURL;

import java.io.File;
import java.io.IOException;
import java.util.ArrayList;
import java.util.Set;
import java.util.UUID;
import java.util.regex.Pattern;

public class SyCrawler extends WebCrawler {
    private final static Pattern FILTERS = Pattern.compile(".*(\\.(css|js|gif|jpg" + "|png|mp3|mp3|zip|gz))$");

    CrawlState crawlState;

    public SyCrawler() {
        crawlState = new CrawlState();
    }

    private static File storageFolder;

    public static void configure(String storageFolderName) {
        storageFolder = new File(storageFolderName);
        if (!storageFolder.exists()) {
            storageFolder.mkdirs();
        }
    }

    @Override
    public boolean shouldVisit(Page page, WebURL url) {
        String href = url.getURL().toLowerCase();
        String type = "OutUSC";
        if (href.contains("marshall.usc.edu")) {
            type = "OK";
        } else if (href.contains("usc.edu")) {
            type = "USC";
        }
        crawlState.discoveredUrls.add(new UrlInfo(href, type));
        return !FILTERS.matcher(href).matches() && type.equals("OK");
    }

    @Override
    public void visit(Page page) {
        String url = page.getWebURL().getURL();
        String contentType = page.getContentType().split(";")[0];
        String extension = "";

        if (contentType.equals("text/html")) { // html
            if (page.getParseData() instanceof HtmlParseData) {
                HtmlParseData htmlParseData = (HtmlParseData) page.getParseData();
                Set<WebURL> links = htmlParseData.getOutgoingUrls();
                crawlState.visitedUrls.add(new UrlInfo(url, page.getContentData().length, links.size(), "text/html"));
            } else {
                crawlState.visitedUrls.add(new UrlInfo(url, page.getContentData().length, 0, "text/html"));
            }
            extension = ".html";
        } else if (contentType.equals("application/msword")) { // doc
            crawlState.visitedUrls.add(new UrlInfo(url, page.getContentData().length, 0, "application/msword"));
            extension = ".doc";
        } else if (contentType.equals("application/pdf")) { // pdf
            crawlState.visitedUrls.add(new UrlInfo(url, page.getContentData().length, 0, "application/pdf"));
            extension = ".pdf";
        } else if (contentType.equals("application/vnd.openxmlformats-officedocument.wordprocessingml.document")) {
            crawlState.visitedUrls.add(new UrlInfo(url, page.getContentData().length, 0, "application/vnd.openxmlformats-officedocument.wordprocessingml.document"));
            extension = ".docx";
        } else {
            crawlState.visitedUrls.add(new UrlInfo(url, page.getContentData().length, 0, "unknown"));
        }

        if (!extension.equals("")) {
            String hashedName = UUID.randomUUID() + extension;
            String filename = storageFolder.getAbsolutePath() + "/" + hashedName;
            try {
                Files.write(page.getContentData(), new File(filename));
            } catch (IOException iox) {
                System.out.println("Failed to write file: " + filename);
            }
        }
    }

    @Override
    protected void handlePageStatusCode(WebURL webUrl, int statusCode, String statusDescription) {
        crawlState.attemptUrls.add(new UrlInfo(webUrl.getURL(), statusCode));
    }

    @Override
    public Object getMyLocalData() {
        return crawlState;
    }
}