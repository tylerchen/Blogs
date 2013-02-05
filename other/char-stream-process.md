基于字符流命令的内容解释
=====

### 1、概述

我们常常需要解释ASCII码的输入流，这些输入流读入时一般是以字节数组的形式，这些输入还会包括命令或控制字符，对应不同的命令或控制字符都有不同的处理方式。而解释和匹配这些命令常常会令我们头痛。这里介绍一种方法可以有效的处理这些命令。

### 2、场景

以VT100 Terminal Control为例，这些控制命令是严谨的不会发生混淆的，他的命令有如：


    <ESC>[c
    <ESC>[{code}0c
    <ESC>[{ROW};{COLUMN}R
    <ESC>[{attr1};...;{attrn}m
    <ESC>[{key};"{string}"p


### 3、分析

上面这些命令主要分为：


    (I)明确的命令（<ESC>[c）
    (II)定长可变的命令（<ESC>[{code}0c、<ESC>[{ROW};{COLUMN}R）
    (III)变长但可穷举的命令（<ESC>[{attr1};...;{attrn}m）
    (IV)变长命令（<ESC>[{key};"{string}"p）
    对于前三种命令的处理都还算好，对于最后变长命令的处理就比较困难，因为其长度是由{key}来决定的，这里不对这种命令进行讨论。
    对于前三种命令最长的长度是10个字节（不带<ESC>，以VT100为例），因为所有的命令及长度都是已知的，所以处理进来比较容易。
    对于这些控制字符的处理比较普遍的做法是使用控制语句，如：
    if(b =='<ESC>'){
      if(nextChar() == '['){
    		if(nextChar == 'c'){
    			//do something
    		}else if(currentChar() == '{ROW}'){
    			//...
    		}
    	}
    }


你会发现这种判断语句写起来真的很晕，特别是使用一个指针来拿nextChar，不能匹配的话还得要回退，不容易想清楚，别人也难以读懂。

### 4、解决办法

使用逐个字符来判断的确不是一个好的方法，这里推荐另一种做法，使用定长字节数据进行判断。这种方法的具体做法如下：

a)把(I)明确的命令使用hash code及对应的处理方法存入map中，其中hash code为该控制命令字节数组的hash code，存储的形式如：map(hashCode, processMethod)。

b)处理(II)定长可变的命令和(III)变长但可穷举的命令，使用类似如下的语句：


    byte[] bs = new byte[len];
    System.arraycopy(src, startPos, bs, 0, len);
    byte b1 = cs[0], b2 = cs[cs.length - 2], be = cs[cs.length - 1];
    if (b1 != '[') {continue;}
    switch (len) {
      case 1:
    		break;
    	case 2:
    		break;
    	case 3:
    		break;
    	case 4:
    		//<ESC>[{attr1};...;{attrn}m    //<ESC>[{code}0c
    		if (be == 'm' || (be == 'c' && b2 == '0')) {
    			//process method
    		}
    		break;
    	case 5:
    		if (be == 'm' || be == 'r' || be == 'H' || be == 'f' || be == 'R' || (be == 'c' && b2 == '0')) {
    			//process method
    		}
    		break;
    	case 6:
    		//...
    		break;
    	case 7:
    		//...
    		break;
    	case 8:
    		//...
    		break;
    	case 9:
    		//...
    		break;
    	case 10:
    		//...
    		break;
    	default:
    		break;
    	}



### 5、整个程序的结构


    Map<Integer, ProcessMethod> map = new HashMap<Integer, ProcessMethod>();
    private List<byte[]> list = Arrays.asList(new byte[1], new byte[2],
      		new byte[3], new byte[4], new byte[5], new byte[6], new byte[7],
    			new byte[8], new byte[9], new byte[10]);
    private void init() {
    	map.put(hashCode(new byte[] { '[', 'c' }), new ProcessMethod(){/* process method */});
    }
    private void process(byte[] bs, int start, int len) {
    	if (map.isEmpty()) {
    		init();
    	}
    	for (int i = start; i < len; i++) {
    		byte b = bs[i];
    		if (b == '<ESC>') {
    			for (int j = 0; j < list.size() && j + i + 1 < len; j++) {
    				byte[] cs = list.get(j);
    				System.arraycopy(bs, i + 1, cs, 0, cs.length);
    				if (map.contains(hashCode(cs))) {
    					//map.get(hashCode(cs)).process();
    					break;
    				} else {
    					if (j + 1 < 3) {
    						continue;
    					}
    					byte b1 = cs[0], b2 = cs[cs.length - 2], be = cs[cs.length - 1];
    					int old = i;
    					if (b1 != '[') {
    						continue;
    					}
    					switch (j + 1) {
    					case 1:
    						break;
    					case 2:
    						break;
    					case 3:
    						break;
    					case 4:
    						//<ESC>[{attr1};...;{attrn}m    //<ESC>[{code}0c
    						if (be == 'm' || (be == 'c' && b2 == '0')) {
    							//new ProcessMethod(){/* process method */}.process();
    						}
    						break;
    					case 5:
    						if (be == 'm' || be == 'r' || be == 'H' || be == 'f' || be == 'R' || (be == 'c' && b2 == '0')) {
    							//new ProcessMethod(){/* process method */}.process();
    						}
    						break;
    					case 6:
    						break;
    					case 7:
    						break;
    					case 8:
    						break;
    					case 9:
    						break;
    					case 10:
    						break;
    					default:
    						break;
    					}
    					if (old != i) {
    						break;
    					}
    				}
    			}
    		}
    	}
    }


