Maven使用备忘
=====

#### Maven资源没有拷贝

在Eclipse中进行clean后，发现没有把resources的文件拷贝到对应的目录，也没有任何的错误提示，在控制台环境中使用Maven进行构建却可以通过。这时可以选择工程，通过Eclipse的Maven插件对工程进行Maven的clean。这时发现Maven的clean无法通过，通过查看错误信息，发现依赖重复，去除重复的依赖后，Maven clean就可以正常执行，这时资源也拷贝到相应的目录了。

这个问题可能Maven插件的问题，或许新版本已经解决了。但遇到异常的情况时，问题就可能出现在Maven上面。

#### Maven中JUnit的Scope问题

有时在Eclipse中执行单元测试，发现运行得很好，但在Maven环境中执行时就会发现单元测试无法通过。这个问题有可能是你把JUnit或DBUnit的Scope写错了。把JUnit和DBUnit的Scope写为test可能就可以解决该问题。
