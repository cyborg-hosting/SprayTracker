//-----------------------------------------------------------------------------
//
// VTFConv - Converts VTF files to multiple PNGs
// By Darkimmortal
// Built around an example program, hence the retarded comments :V
//
//-----------------------------------------------------------------------------

// Required include files.
#ifdef HAVE_CONFIG_H
#include "config.h"
#endif /* HAVE_CONFIG_H */

#include <IL/il.h>
#include <stdio.h>
#include <string.h>
#include <stdlib.h>


/* We would need ILU just because of iluErrorString() function... */
/* So make it possible for both with and without ILU!  */
#ifdef ILU_ENABLED
#include <IL/ilu.h>
#define PRINT_ERROR_MACRO printf("Error: %s\n", iluErrorString(Error))
#else /* not ILU_ENABLED */
#define PRINT_ERROR_MACRO printf("Error: 0x%X\n", (unsigned int)Error)
#endif /* not ILU_ENABLED */


char *str_replace(char * t1, char * t2, char * t6){
	char*t4;
	char*t5=malloc(0);

	while(strstr(t6,t1)){
		t4=strstr(t6,t1);
		strncpy(t5+strlen(t5),t6,t4-t6);
		strcat(t5,t2);
		t4+=strlen(t1);
		t6=t4;
	}
	return strcat(t5,t4);
}


int main(int argc, char **argv)
{
	ILuint	ImgId;
	ILenum	Error;

	// We use the filename specified in the first argument of the command-line.
	if (argc < 2) {
		fprintf(stderr, "VTFConv - Converts VTF files to multiple PNGs\n");
		fprintf(stderr, "Usage: vtfconv <file>\n");
		return 1;
	}

	// Check if the shared lib's version matches the executable's version.
	if (ilGetInteger(IL_VERSION_NUM) < IL_VERSION) {
		printf("DevIL version is different...exiting!\n");
		return 2;
	}

	// Initialize DevIL.
	ilInit();
#ifdef ILU_ENABLED
	iluInit();
#endif 

	// Generate the main image name to use.
	ilGenImages(1, &ImgId);

	// Bind this image name.
	ilBindImage(ImgId);

	// Loads the image specified by File into the image named by ImgId.
	if (!ilLoadImage(argv[1])) {
		printf("Could not open file...exiting.\n");
		return 3;
	}

	// Display the image's dimensions to the end user.
	/*printf("Width: %d  Height: %d  Depth: %d  Bpp: %d\n",
	       ilGetInteger(IL_IMAGE_WIDTH),
	       ilGetInteger(IL_IMAGE_HEIGHT),
	       ilGetInteger(IL_IMAGE_DEPTH),
	       ilGetInteger(IL_IMAGE_BITS_PER_PIXEL));*/

	// Enable this to let us overwrite the destination file if it already exists.
	ilEnable(IL_FILE_OVERWRITE);
	ilConvertImage(IL_RGBA, IL_UNSIGNED_BYTE);

	// If argv[2] is present, we save to this filename, else we save to test.tga.
	//if (argc > 2)
	//	ilSaveImage(argv[2]);
	//else
	//	ilSaveImage("test.tga");
	
	// str_replace(".vtf", "", argv[1])
	const unsigned int lNumImages = ilGetInteger(IL_NUM_IMAGES)+1;
	unsigned int derp = 0;
	char flerp[256];
	//while(ilActiveImage(derp) != IL_FALSE){
	for(derp = 0; derp < lNumImages; derp ++){
		//if(ilActiveImage(derp) == IL_FALSE) break;
		sprintf(flerp, "%s.%d.png", argv[1], derp);
		printf("%s\n", flerp);
		ilSaveImage(flerp);
	//	derp ++;
		if(derp < lNumImages-1)
			if(ilActiveImage(1) == IL_FALSE) break;
	}
	printf("Saved frames: %d/%d\n", derp, lNumImages);

	// We're done with the image, so let's delete it.
	//ilDeleteImages(1, &ImgId);

	// Simple Error detection loop that displays the Error to the user in a human-readable form.
	while ((Error = ilGetError())) {
		PRINT_ERROR_MACRO;}

	return 0;

}
