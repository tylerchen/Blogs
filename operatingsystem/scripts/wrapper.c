#include <stdlib.h>
#include <sys/types.h>
#include <unistd.h>
#include <stdio.h>
#include <string.h>

int
main (int argc, char *argv[])
{
   setuid (0);

   /* WARNING: Only use an absolute path to the script to execute,
    *          a malicious user might fool the binary and execute
    *          arbitary commands if not.
    * */
   char *args;
   int i=0;
   int len=0;
   for(i=1;i<argc;i++){
     len+=strlen(argv[i])+1;
   }
   args = (char*)(malloc(len));
   for(i=1;i<argc;i++){
     strcat(args,argv[i]);
     strcat(args," ");
   }
   printf("command: %s\n",args);
   system (args);
   free(args);
   args=NULL;

   return 0;
 }
