StringHelper
====

字符串处理工具类，包括以下方法：

1. 连接各个字符串，null为""。

        public static String concat(String... strs);

2. 替换XML字符，包括：&, <, >, ", '。

        public static String replaceXmlChar(String str);

3. 占位符替换，由Map中的key替换指定的值，如："hello {name}" + "world" => hello world。

        public static String replaceBlock(String str, Map<String, Object> replaces, String blank);

4. 占位符替换，按顺序替换，如："hello {name}" + "world" => hello world。

        public static String replaceBlock(String str, Object[] replaces, String blank);

5. 连接各个路径，连接符Windows为"\"， Unix/Linux为"/"。

        public static String pathConcat(String... paths);

6. 清理路径，如C:\\\a\\b => c:\a\b。

        public static String pathBuild(String str, String fileSeparator);


相关下载，[StringHelper.java](StringHelper.java?raw=true)
