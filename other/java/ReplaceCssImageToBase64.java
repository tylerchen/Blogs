import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.InputStreamReader;
import java.io.OutputStreamWriter;

/*******************************************************************************
 * Copyright (c) 2014-1-26 @author <a href="mailto:iffiff1@hotmail.com">Tyler Chen</a>.
 * All rights reserved.
 *
 * Contributors:
 *     <a href="mailto:iffiff1@hotmail.com">Tyler Chen</a> - initial API and implementation
 ******************************************************************************/

/**
 * @author <a href="mailto:iffiff1@hotmail.com">Tyler Chen</a> 
 * @since 2014-1-26
 */
public class ReplaceCssImageToBase64 {

	public static void main(String[] args) {
		String floder = "G:/ForTraining/fixRedmineImgPath/";
		File file = new File(floder);
		String[] files = file.list();
		for (String fileName : files) {
			if (!fileName.endsWith(".css")) {
				continue;
			}
			System.out.println("fileName:" + fileName);
			StringBuilder readFile = readFile(floder + fileName);
			filterUrl(readFile);
			System.out.println(readFile);
			writeFile(floder + "css/" + fileName, readFile);
		}
	}

	public static StringBuilder readFile(String fileName) {
		StringBuilder sb = new StringBuilder(1024);
		File file = new File(fileName);
		try {
			FileInputStream is = new FileInputStream(file);
			BufferedReader br = new BufferedReader(new InputStreamReader(is,
					"UTF-8"));
			String line = null;
			while ((line = br.readLine()) != null) {
				sb.append(line).append("\n");
			}
			try {
				is.close();
			} catch (Exception e) {
				e.printStackTrace();
			}
		} catch (Exception e) {
		}
		return sb;
	}

	public static void writeFile(String fileName, StringBuilder content) {
		File file = new File(fileName);
		try {
			if (!file.exists()) {
				file.createNewFile();
			} else {
				file.delete();
				file.createNewFile();
			}
			BufferedWriter out = new BufferedWriter(new OutputStreamWriter(
					new FileOutputStream(file), "UTF-8"));
			//BufferedWriter out = new BufferedWriter(new FileWriter(file));
			out.append(content);
			try {
				out.flush();
				out.close();
			} catch (Exception e) {
				e.printStackTrace();
			}
		} catch (Exception e) {
			e.printStackTrace();
		}
	}

	public static void filterUrl(StringBuilder content) {
		int indexStart = 0;
		while ((indexStart = content.indexOf("url", indexStart)) > 0) {
			if (findChar(true, ':', content, indexStart - 1)
					&& findChar(false, '(', content, indexStart + 3)) {
				int indexEnd = content.indexOf(")", indexStart + 3);
				if (indexEnd > 0) {
					System.out.println(content.substring(indexStart,indexEnd + 1));
					String encode = getBase64Img(content.substring(indexStart,
							indexEnd + 1));
					if (encode.length() > 0) {
						content.replace(indexStart, indexEnd + 1, encode);
						indexStart = indexStart + encode.length();
					} else {
						indexStart = indexEnd;
					}
				} else {
					indexStart = indexEnd;
				}
			} else {
				indexStart = indexStart + 3;
			}
		}
	}

	public static String getBase64Img(String imageUrl/*url(../images/projects.png)*/) {
		String url = imageUrl.substring(imageUrl.indexOf('/') + 1);
		url = url.substring(0, url.indexOf(')'));
		File image = new File("G:/ForTraining/fixRedmineImgPath/" + url);
		System.out.println("G:/ForTraining/fixRedmineImgPath/" + url);
		if (image.exists()) {
			try {
				FileInputStream is = new FileInputStream(image);
				byte[] bs = new byte[Long.valueOf(image.length()).intValue()];
				is.read(bs);
				try {
				} catch (Exception e) {
					is.close();
				}
				return "url(\"data:image/"
						+ url.substring(url.lastIndexOf('.') + 1) + ";base64,"
						+ Base64.encodeToString(bs, false) + "\")";
			} catch (Exception e) {
			}
		}
		return "";
	}

	public static boolean findChar(boolean reverse, char c,
			StringBuilder content, int startPos) {
		StringBuilder sb = new StringBuilder(512);
		if (reverse) {
			for (int i = startPos; i > 0; i--) {
				char charAt = content.charAt(i);
				sb.append(charAt);
				if (c == charAt) {
					return sb.toString().trim().length() == 1;
				}
			}
		} else {
			for (int i = startPos; i < content.length(); i++) {
				char charAt = content.charAt(i);
				sb.append(charAt);
				if (c == charAt) {
					return sb.toString().trim().length() == 1;
				}
			}
		}
		return false;
	}
}
