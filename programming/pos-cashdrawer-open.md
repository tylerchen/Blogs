开启钱箱(CashDrawer)核心代码
======

这里的代码只是POS驱动中的一部份


    private static void openDrawer(String portName, PosPrinter pp/*这是POS机的配置*/)
            throws Exception {
        CommPortIdentifier port = null;
        CommPort open = null;
        try {
            port = CommPortIdentifier.getPortIdentifier(portName);
            open = port.open(portName, 100);
            open.setInputBufferSize(32);
            open.setOutputBufferSize(32);
            OutputStream outputStream = open.getOutputStream();
            //设置开启钱箱的脉冲频率，一般不用设置，默认就行
            if (pp.getPc().hasDrawerConfig()) {
                if (pp.getPc().getDrawerRate() == 2400) {
                    outputStream.write(EpsonPosPrinterCommand.ESC_DRAWER_RATE_2400);
                } else {
                    outputStream.write(EpsonPosPrinterCommand.ESC_DRAWER_RATE_9600);
                }
                int ontime = pp.getPc().getDrawerOntime();
                int offtime = pp.getPc().getDrawerOfftime();
                outputStream.write(EpsonPosPrinterCommand
                        .generatePulse(ontime, offtime));
                outputStream.write(EpsonPosPrinterCommand.ESC_OPEN_DRAWER);
            } else {//默认设置
                outputStream.write(EpsonPosPrinterCommand.ESC_DRAWER_RATE_9600);
                int ontime = 50;
                int offtime = 50;
                outputStream.write(EpsonPosPrinterCommand.generatePulse(ontime, offtime));
                outputStream.write(EpsonPosPrinterCommand.ESC_OPEN_DRAWER);
            }
            outputStream.flush();
            java.util.concurrent.TimeUnit.SECONDS.sleep(1);
            outputStream.close();
        } catch (Exception e) {
            e.printStackTrace();
            if (e instanceof IOException
                && e.getMessage()
                        .startsWith("Resource temporarily unavailable")) {
            } else {
                throw e;
            }
        } finally {
            if (port != null && port.isCurrentlyOwned()) {
                open.close();
            }
        }
    }

