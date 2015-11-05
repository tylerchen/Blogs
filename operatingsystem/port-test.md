test.sh
====
    #!/bin/bash
    
    test=`python << EOF
    import socket
    sk = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    sk.settimeout(1)
    try:
        sk.connect(("192.168.0.1",21))
        print 'OK'
    except Exception:
        print 'FAIL'
    sk.close()
    EOF`
    
    echo $test
