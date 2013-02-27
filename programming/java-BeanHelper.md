一个简单的对象属性复制工具类
=======

该工具类目前只有一个方法：

    public static <T> T copyProperties(Object dest, Object orig);

能实现的功能：

1. 对象与对象的属性复制

2. Map与对象的相互复制

3. Map与Map的复制

4. 所有属性复制原则上都是浅拷贝

5. 如果属性也是一种复杂类型，则不支持

6. 属性名称一样，但类型不一样的，如java.sql.Date与java.util.Date，String与Boolean，String与Number之间的转换

