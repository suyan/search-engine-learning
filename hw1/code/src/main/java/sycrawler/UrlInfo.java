package sycrawler;

public class UrlInfo {
    public int statusCode;
    public String url;
    public int size;
    public int links;
    public String type;

    public UrlInfo(String url, int statusCode) {
        this.url = url;
        this.statusCode = statusCode;
    }

    public UrlInfo(String url, String type) {
        this.url = url;
        this.type = type;
    }

    public UrlInfo(String url, int size, int links, String type) {
        this.url = url;
        this.size = size;
        this.links = links;
        this.type = type;
    }
}
