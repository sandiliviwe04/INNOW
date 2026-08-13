<svg
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 1000 260"
    width="1000"
    height="260"
    role="img"
    aria-labelledby="title desc"
>

    <title id="title">INNOW Digital Attendance System</title>

    <desc id="desc">
        INNOW logo featuring a staff identity shield with verification checkmark.
    </desc>



    <!-- ========================================= -->
    <!-- DEFINITIONS -->
    <!-- ========================================= -->

    <defs>

        <!-- INNOW RED -->
        <linearGradient
            id="redGradient"
            x1="0%"
            y1="0%"
            x2="100%"
            y2="100%"
        >
            <stop
                offset="0%"
                stop-color="#FF2B2F"
            />

            <stop
                offset="55%"
                stop-color="#E52529"
            />

            <stop
                offset="100%"
                stop-color="#B91418"
            />
        </linearGradient>



        <!-- DARK -->
        <linearGradient
            id="darkGradient"
            x1="0%"
            y1="0%"
            x2="100%"
            y2="100%"
        >
            <stop
                offset="0%"
                stop-color="#252A30"
            />

            <stop
                offset="100%"
                stop-color="#111418"
            />
        </linearGradient>



        <!-- SHADOW -->
        <filter
            id="shadow"
            x="-30%"
            y="-30%"
            width="160%"
            height="160%"
        >

            <feDropShadow
                dx="0"
                dy="5"
                stdDeviation="5"
                flood-color="#000000"
                flood-opacity="0.18"
            />

        </filter>

    </defs>



    <!-- ========================================= -->
    <!-- SHIELD / STAFF VERIFICATION ICON -->
    <!-- ========================================= -->

    <g
        transform="translate(25, 20)"
        filter="url(#shadow)"
    >

        <!-- OUTER RED SHIELD -->

        <path
            d="
                M110 8
                L205 43
                L205 125
                C205 181 168 214 110 237
                C52 214 15 181 15 125
                L15 43
                Z
            "
            fill="url(#redGradient)"
        />



        <!-- WHITE SHIELD BORDER -->

        <path
            d="
                M110 27
                L187 56
                L187 124
                C187 166 159 194 110 216
                C61 194 33 166 33 124
                L33 56
                Z
            "
            fill="#FFFFFF"
        />



        <!-- DARK INNER SHIELD -->

        <path
            d="
                M110 38
                L175 63
                L175 122
                C175 158 151 183 110 201
                C69 183 45 158 45 122
                L45 63
                Z
            "
            fill="url(#darkGradient)"
        />



        <!-- USER HEAD -->

        <circle
            cx="110"
            cy="83"
            r="25"
            fill="#FFFFFF"
        />



        <!-- USER BODY -->

        <path
            d="
                M67 143
                C70 115 87 103 110 103
                C133 103 150 115 153 143
                L153 150
                L67 150
                Z
            "
            fill="#FFFFFF"
        />



        <!-- WHITE VERIFICATION CIRCLE -->

        <circle
            cx="173"
            cy="157"
            r="42"
            fill="#FFFFFF"
        />



        <!-- RED VERIFICATION CIRCLE -->

        <circle
            cx="173"
            cy="157"
            r="35"
            fill="url(#redGradient)"
        />



        <!-- CHECKMARK -->

        <path
            d="
                M153 157
                L167 171
                L194 141
            "
            fill="none"
            stroke="#FFFFFF"
            stroke-width="10"
            stroke-linecap="round"
            stroke-linejoin="round"
        />

    </g>



    <!-- ========================================= -->
    <!-- RED DIVIDER -->
    <!-- ========================================= -->

    <rect
        x="285"
        y="55"
        width="3"
        height="145"
        rx="1.5"
        fill="#E52529"
    />



    <!-- ========================================= -->
    <!-- INNOW WORDMARK -->
    <!-- ========================================= -->

    <!--
        INN = DARK
        OW  = RED

        Using tspans inside ONE text element
        removes the unwanted space between INN and OW.
    -->

    <text
        x="330"
        y="138"
        font-family="Arial, Helvetica, sans-serif"
        font-size="112"
        font-weight="800"
        letter-spacing="-7"
    >

        <tspan
            fill="url(#darkGradient)"
        >INN</tspan><tspan
            fill="url(#redGradient)"
        >OW</tspan>

    </text>



    <!-- ========================================= -->
    <!-- DIGITAL ATTENDANCE SYSTEM -->
    <!-- ========================================= -->

    <text
        x="333"
        y="180"
        font-family="Arial, Helvetica, sans-serif"
        font-size="21"
        font-weight="500"
        letter-spacing="6"
        fill="#252A30"
    >
        DIGITAL ATTENDANCE SYSTEM
    </text>

</svg>