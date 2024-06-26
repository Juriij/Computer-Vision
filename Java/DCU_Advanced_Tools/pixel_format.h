//pixfmt.h/avutil.java

/** planar YUV 4:2:0, 12bpp, (1 Cr & Cb sample per 2x2 Y samples) */
    AV_PIX_FMT_YUV420P = 0,
    /** packed YUV 4:2:2, 16bpp, Y0 Cb Y1 Cr */
    AV_PIX_FMT_YUYV422 = 1,
    /** packed RGB 8:8:8, 24bpp, RGBRGB... */
    AV_PIX_FMT_RGB24 = 2,
    /** packed RGB 8:8:8, 24bpp, BGRBGR... */
    AV_PIX_FMT_BGR24 = 3,
    /** planar YUV 4:2:2, 16bpp, (1 Cr & Cb sample per 2x1 Y samples) */
    AV_PIX_FMT_YUV422P = 4,
    /** planar YUV 4:4:4, 24bpp, (1 Cr & Cb sample per 1x1 Y samples) */
    AV_PIX_FMT_YUV444P = 5,
    /** planar YUV 4:1:0,  9bpp, (1 Cr & Cb sample per 4x4 Y samples) */
    AV_PIX_FMT_YUV410P = 6,
    /** planar YUV 4:1:1, 12bpp, (1 Cr & Cb sample per 4x1 Y samples) */
    AV_PIX_FMT_YUV411P = 7,
    /**        Y        ,  8bpp */
    AV_PIX_FMT_GRAY8 = 8,
    /**        Y        ,  1bpp, 0 is white, 1 is black, in each byte pixels are ordered from the msb to the lsb */
    AV_PIX_FMT_MONOWHITE = 9,
    /**        Y        ,  1bpp, 0 is black, 1 is white, in each byte pixels are ordered from the msb to the lsb */
    AV_PIX_FMT_MONOBLACK = 10,
    /** 8 bit with PIX_FMT_RGB32 palette */
    AV_PIX_FMT_PAL8 = 11,
    /** planar YUV 4:2:0, 12bpp, full scale (JPEG), deprecated in favor of PIX_FMT_YUV420P and setting color_range */
    AV_PIX_FMT_YUVJ420P = 12,
    /** planar YUV 4:2:2, 16bpp, full scale (JPEG), deprecated in favor of PIX_FMT_YUV422P and setting color_range */
    AV_PIX_FMT_YUVJ422P = 13,
    /** planar YUV 4:4:4, 24bpp, full scale (JPEG), deprecated in favor of PIX_FMT_YUV444P and setting color_range */
    AV_PIX_FMT_YUVJ444P = 14,
    /** XVideo Motion Acceleration via common packet passing */
    AV_PIX_FMT_XVMC_MPEG2_MC = 15,
    AV_PIX_FMT_XVMC_MPEG2_IDCT = 16;
    /** packed YUV 4:2:2, 16bpp, Cb Y0 Cr Y1 */
    AV_PIX_FMT_UYVY422 = 17,
    /** packed YUV 4:1:1, 12bpp, Cb Y0 Y1 Cr Y2 Y3 */
    AV_PIX_FMT_UYYVYY411 = 18,
    /** packed RGB 3:3:2,  8bpp, (msb)2B 3G 3R(lsb) */
    AV_PIX_FMT_BGR8 = 19,
    /** packed RGB 1:2:1 bitstream,  4bpp, (msb)1B 2G 1R(lsb), a byte contains two pixels, the first pixel in the byte is the one composed by the 4 msb bits */
    AV_PIX_FMT_BGR4 = 20,
    /** packed RGB 1:2:1,  8bpp, (msb)1B 2G 1R(lsb) */
    AV_PIX_FMT_BGR4_BYTE = 21,
    /** packed RGB 3:3:2,  8bpp, (msb)2R 3G 3B(lsb) */
    AV_PIX_FMT_RGB8 = 22,
    /** packed RGB 1:2:1 bitstream,  4bpp, (msb)1R 2G 1B(lsb), a byte contains two pixels, the first pixel in the byte is the one composed by the 4 msb bits */
    AV_PIX_FMT_RGB4 = 23,
    /** packed RGB 1:2:1,  8bpp, (msb)1R 2G 1B(lsb) */
    AV_PIX_FMT_RGB4_BYTE = 24,
    /** planar YUV 4:2:0, 12bpp, 1 plane for Y and 1 plane for the UV components, which are interleaved (first byte U and the following byte V) */
    AV_PIX_FMT_NV12 = 25,
    /** as above, but U and V bytes are swapped */
    AV_PIX_FMT_NV21 = 26,

    /** packed ARGB 8:8:8:8, 32bpp, ARGBARGB... */
    AV_PIX_FMT_ARGB = 27,
    /** packed RGBA 8:8:8:8, 32bpp, RGBARGBA... */
    AV_PIX_FMT_RGBA = 28,
    /** packed ABGR 8:8:8:8, 32bpp, ABGRABGR... */
    AV_PIX_FMT_ABGR = 29,
    /** packed BGRA 8:8:8:8, 32bpp, BGRABGRA... */
    AV_PIX_FMT_BGRA = 30,

    /**        Y        , 16bpp, big-endian */
    AV_PIX_FMT_GRAY16BE = 31,
    /**        Y        , 16bpp, little-endian */
    AV_PIX_FMT_GRAY16LE = 32,
    /** planar YUV 4:4:0 (1 Cr & Cb sample per 1x2 Y samples) */
    AV_PIX_FMT_YUV440P = 33,
    /** planar YUV 4:4:0 full scale (JPEG), deprecated in favor of PIX_FMT_YUV440P and setting color_range */
    AV_PIX_FMT_YUVJ440P = 34,
    /** planar YUV 4:2:0, 20bpp, (1 Cr & Cb sample per 2x2 Y & A samples) */
    AV_PIX_FMT_YUVA420P = 35,
    /** H.264 HW decoding with VDPAU, data[0] contains a vdpau_render_state struct which contains the bitstream of the slices as well as various fields extracted from headers */
    AV_PIX_FMT_VDPAU_H264 = 36,
    /** MPEG-1 HW decoding with VDPAU, data[0] contains a vdpau_render_state struct which contains the bitstream of the slices as well as various fields extracted from headers */
    AV_PIX_FMT_VDPAU_MPEG1 = 37,
    /** MPEG-2 HW decoding with VDPAU, data[0] contains a vdpau_render_state struct which contains the bitstream of the slices as well as various fields extracted from headers */
    AV_PIX_FMT_VDPAU_MPEG2 = 38,
    /** WMV3 HW decoding with VDPAU, data[0] contains a vdpau_render_state struct which contains the bitstream of the slices as well as various fields extracted from headers */
    AV_PIX_FMT_VDPAU_WMV3 = 39,
    /** VC-1 HW decoding with VDPAU, data[0] contains a vdpau_render_state struct which contains the bitstream of the slices as well as various fields extracted from headers */
    AV_PIX_FMT_VDPAU_VC1 = 40,
    /** packed RGB 16:16:16, 48bpp, 16R, 16G, 16B, the 2-byte value for each R/G/B component is stored as big-endian */
    AV_PIX_FMT_RGB48BE = 41,
    /** packed RGB 16:16:16, 48bpp, 16R, 16G, 16B, the 2-byte value for each R/G/B component is stored as little-endian */
    AV_PIX_FMT_RGB48LE = 42,

    /** packed RGB 5:6:5, 16bpp, (msb)   5R 6G 5B(lsb), big-endian */
    AV_PIX_FMT_RGB565BE = 43,
    /** packed RGB 5:6:5, 16bpp, (msb)   5R 6G 5B(lsb), little-endian */
    AV_PIX_FMT_RGB565LE = 44,
    /** packed RGB 5:5:5, 16bpp, (msb)1A 5R 5G 5B(lsb), big-endian, most significant bit to 0 */
    AV_PIX_FMT_RGB555BE = 45,
    /** packed RGB 5:5:5, 16bpp, (msb)1A 5R 5G 5B(lsb), little-endian, most significant bit to 0 */
    AV_PIX_FMT_RGB555LE = 46,

    /** packed BGR 5:6:5, 16bpp, (msb)   5B 6G 5R(lsb), big-endian */
    AV_PIX_FMT_BGR565BE = 47,
    /** packed BGR 5:6:5, 16bpp, (msb)   5B 6G 5R(lsb), little-endian */
    AV_PIX_FMT_BGR565LE = 48,
    /** packed BGR 5:5:5, 16bpp, (msb)1A 5B 5G 5R(lsb), big-endian, most significant bit to 1 */
    AV_PIX_FMT_BGR555BE = 49,
    /** packed BGR 5:5:5, 16bpp, (msb)1A 5B 5G 5R(lsb), little-endian, most significant bit to 1 */
    AV_PIX_FMT_BGR555LE = 50,

    /** HW acceleration through VA API at motion compensation entry-point, Picture.data[3] contains a vaapi_render_state struct which contains macroblocks as well as various fields extracted from headers */
    AV_PIX_FMT_VAAPI_MOCO = 51,
    /** HW acceleration through VA API at IDCT entry-point, Picture.data[3] contains a vaapi_render_state struct which contains fields extracted from headers */
    AV_PIX_FMT_VAAPI_IDCT = 52,
    /** HW decoding through VA API, Picture.data[3] contains a vaapi_render_state struct which contains the bitstream of the slices as well as various fields extracted from headers */
    AV_PIX_FMT_VAAPI_VLD = 53,

    /** planar YUV 4:2:0, 24bpp, (1 Cr & Cb sample per 2x2 Y samples), little-endian */
    AV_PIX_FMT_YUV420P16LE = 54,
    /** planar YUV 4:2:0, 24bpp, (1 Cr & Cb sample per 2x2 Y samples), big-endian */
    AV_PIX_FMT_YUV420P16BE = 55,
    /** planar YUV 4:2:2, 32bpp, (1 Cr & Cb sample per 2x1 Y samples), little-endian */
    AV_PIX_FMT_YUV422P16LE = 56,
    /** planar YUV 4:2:2, 32bpp, (1 Cr & Cb sample per 2x1 Y samples), big-endian */
    AV_PIX_FMT_YUV422P16BE = 57,
    /** planar YUV 4:4:4, 48bpp, (1 Cr & Cb sample per 1x1 Y samples), little-endian */
    AV_PIX_FMT_YUV444P16LE = 58,
    /** planar YUV 4:4:4, 48bpp, (1 Cr & Cb sample per 1x1 Y samples), big-endian */
    AV_PIX_FMT_YUV444P16BE = 59,
    /** MPEG4 HW decoding with VDPAU, data[0] contains a vdpau_render_state struct which contains the bitstream of the slices as well as various fields extracted from headers */
    AV_PIX_FMT_VDPAU_MPEG4 = 60,
    /** HW decoding through DXVA2, Picture.data[3] contains a LPDIRECT3DSURFACE9 pointer */
    AV_PIX_FMT_DXVA2_VLD = 61,

    /** packed RGB 4:4:4, 16bpp, (msb)4A 4R 4G 4B(lsb), little-endian, most significant bits to 0 */
    AV_PIX_FMT_RGB444LE = 62,
    /** packed RGB 4:4:4, 16bpp, (msb)4A 4R 4G 4B(lsb), big-endian, most significant bits to 0 */
    AV_PIX_FMT_RGB444BE = 63,
    /** packed BGR 4:4:4, 16bpp, (msb)4A 4B 4G 4R(lsb), little-endian, most significant bits to 1 */
    AV_PIX_FMT_BGR444LE = 64,
    /** packed BGR 4:4:4, 16bpp, (msb)4A 4B 4G 4R(lsb), big-endian, most significant bits to 1 */
    AV_PIX_FMT_BGR444BE = 65,
    /** 8bit gray, 8bit alpha */
    AV_PIX_FMT_YA8 = 66,
    // etc