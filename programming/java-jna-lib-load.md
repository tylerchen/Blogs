动态连接库在不同平台的加载
=========

动态连接库在不同平台的加载，代码来源于JNA项目 

原理把动态连接库按平台架构进行分类存放，加载时就把对应的平台架构的动态连接库解压到临时目录再进行加载，这样就不需要把动态连接放到system32或lib等指定的目录下。 

以下是动态连接放置的路径结构：

    rxtxlib 
    |_______darwin 
    |_______linux-amd64 
    |_______linux-i386 
    |_______linux-i686 
    |_______linux-ia64 
    |_______linux-x86_64 
    |_______sunos-sparc 
    |_______sunos-sparc32 
    |_______sunos-sparc64 
    |_______sunos-x86 
    |_______win32-x86 


    package gnu.io;
    
    import java.io.File;
    import java.io.FileOutputStream;
    import java.io.IOException;
    import java.io.InputStream;
    import java.io.UnsupportedEncodingException;
    import java.net.URL;
    import java.net.URLDecoder;
    import java.util.HashMap;
    import java.util.Iterator;
    import java.util.Map;
    
    public class LoadLibrary {
    
      private static Map nativeLibMap = new HashMap();
    
    	private static String getNativeLibraryResourcePath() {
    		String arch = System.getProperty("os.arch");
    		String osPrefix;
    		if (Platform.isWindows()) {
    			osPrefix = "win32-" + arch;
    		} else if (Platform.isMac()) {
    			osPrefix = "darwin";
    		} else if (Platform.isLinux()) {
    			osPrefix = "linux-" + arch;
    		} else if (Platform.isSolaris()) {
    			osPrefix = "sunos-" + arch;
    		} else {
    			osPrefix = System.getProperty("os.name").toLowerCase();
    			int space = osPrefix.indexOf(" ");
    			if (space != -1) {
    				osPrefix = osPrefix.substring(0, space);
    			}
    			osPrefix += "-" + arch;
    		}
    		return "/rxtxlib/" + osPrefix;
    	}
    
    	private static String getNativeLibraryResourceSuffix() {
    		String arch = System.getProperty("os.arch");
    		String osPrefix;
    		if (Platform.isWindows()) {
    			osPrefix = ".dll";
    		} else if (Platform.isMac()) {
    			osPrefix = ".jnilib";
    		} else if (Platform.isLinux()) {
    			osPrefix = ".so";
    		} else if (Platform.isSolaris()) {
    			osPrefix = ".so";
    		} else {
    			osPrefix = ".so";
    		}
    		return osPrefix;
    	}
    
    	public static void loadNativeLibrary(String nativeLibName) {
    
    		if (nativeLibMap.get(nativeLibName) != null) {
    			System.load(nativeLibMap.get(nativeLibName).toString());
    			return;
    		}
    
    		String libname = System.mapLibraryName(nativeLibName);
    		String resourceName = getNativeLibraryResourcePath() + "/" + libname;
    		URL url = LoadLibrary.class.getResource(resourceName);
    
    		// Add an ugly hack for OpenJDK (soylatte) - JNI libs use the usual .dylib extension
    		if (url == null && Platform.isMac() && resourceName.endsWith(".dylib")) {
    			resourceName = resourceName.substring(0, resourceName
    					.lastIndexOf(".dylib"))
    							+ ".jnilib";
    			url = LoadLibrary.class.getResource(resourceName);
    		}
    		if (url == null) {
    			throw new UnsatisfiedLinkError("jnidispatch (" + resourceName
    											+ ") not found in resource path");
    		}
    
    		File lib = null;
    		if (url.getProtocol().toLowerCase().equals("file")) {
    			try {
    				lib = new File(URLDecoder.decode(url.getPath(), "UTF8"));
    			} catch (UnsupportedEncodingException e) {
    				throw new Error("JRE is unexpectedly missing UTF8 encoding");
    			}
    		} else {
    			InputStream is = LoadLibrary.class
    					.getResourceAsStream(resourceName);
    			if (is == null) {
    				throw new Error("Can't obtain jnidispatch InputStream");
    			}
    
    			FileOutputStream fos = null;
    			try {
    				// Suffix is required on windows, or library fails to load
    				// Let Java pick the suffix
    				lib = File.createTempFile(nativeLibName, null);
    				lib.deleteOnExit();
    				// Have to remove the temp file after VM exit on w32
    				if (Platform.isWindows() && nativeLibMap.isEmpty()) {
    					Runtime.getRuntime().addShutdownHook(new W32Cleanup());
    				}
    				fos = new FileOutputStream(lib);
    				int count;
    				byte[] buf = new byte[1024];
    				while ((count = is.read(buf, 0, buf.length)) > 0) {
    					fos.write(buf, 0, count);
    				}
    			} catch (IOException e) {
    				throw new Error(
    						"Failed to create temporary file for jnidispatch library",
    						e);
    			} finally {
    				try {
    					is.close();
    				} catch (IOException e) {}
    				if (fos != null) {
    					try {
    						fos.close();
    					} catch (IOException e) {}
    				}
    			}
    		}
    
    		nativeLibMap.put(nativeLibName, lib.getAbsolutePath());
    
    		System.load(lib.getAbsolutePath());
    	}
    
    	public static class W32Cleanup extends Thread {
    
    		public W32Cleanup() {}
    
    		public void run() {
    			StringBuffer sb = new StringBuffer();
    			if (!nativeLibMap.isEmpty()) {
    				for (Iterator it = nativeLibMap.values().iterator(); it
    						.hasNext();) {
    					sb.append(it.next()).append(";");
    				}
    			}
    			nativeLibMap.clear();
    			System.out.println(sb.toString());
    			try {
    				Runtime.getRuntime()
    						.exec(
    								new String[] {
    										System.getProperty("java.home")
    												+ "/bin/java", "-cp",
    										System.getProperty("java.class.path"),
    										getClass().getName(), sb.toString() });
    			} catch (IOException e) {
    				e.printStackTrace();
    			}
    		}
    
    		public static void main(String[] args) {
    			StringBuffer sb = new StringBuffer();
    			if (args != null && args.length > 0) {
    				for (int i = 0; i < args.length; i++) {
    					sb.append(args[i]);
    				}
    			}
    			if (sb.length() > 0) {
    				String[] fileNames = sb.toString().split(";");
    				for (int i = 0; i < fileNames.length; i++) {
    					File file = new File(fileNames[i]);
    					if (file.exists()) {
    						long start = System.currentTimeMillis();
    						while (!file.delete() && file.exists()) {
    							try {
    								Thread.sleep(10);
    							} catch (InterruptedException e) {}
    							if (System.currentTimeMillis() - start > 1000)
    								break;
    						}
    					}
    				}
    			}
    			System.exit(0);
    		}
    	}
    
    	public final static class Platform {
    		private static final int UNSPECIFIED = -1;
    		private static final int MAC = 0;
    		private static final int LINUX = 1;
    		private static final int WINDOWS = 2;
    		private static final int SOLARIS = 3;
    		private static final int FREEBSD = 4;
    		private static final int osType;
    
    		static {
    			String osName = System.getProperty("os.name");
    			if (osName.startsWith("Linux")) {
    				osType = LINUX;
    			} else if (osName.startsWith("Mac") || osName.startsWith("Darwin")) {
    				osType = MAC;
    			} else if (osName.startsWith("Windows")) {
    				osType = WINDOWS;
    			} else if (osName.startsWith("Solaris")
    						|| osName.startsWith("SunOS")) {
    				osType = SOLARIS;
    			} else if (osName.startsWith("FreeBSD")) {
    				osType = FREEBSD;
    			} else {
    				osType = UNSPECIFIED;
    			}
    		}
    
    		private Platform() {}
    
    		public static final boolean isMac() {
    			return osType == MAC;
    		}
    
    		public static final boolean isLinux() {
    			return osType == LINUX;
    		}
    
    		public static final boolean isWindows() {
    			return osType == WINDOWS;
    		}
    
    		public static final boolean isSolaris() {
    			return osType == SOLARIS;
    		}
    
    		public static final boolean isFreeBSD() {
    			return osType == FREEBSD;
    		}
    
    		public static final boolean isX11() {
    			// TODO: check FS or do some other X11-specific test
    			return !Platform.isWindows() && !Platform.isMac();
    		}
    	}
    }



下载[rxtxnativelib.jar](rxtxnativelib.jar?raw=true)
