package main

import (
	"fmt"
	"os"
	"strings"
)

var chars [][]string

func main() {
	filenames := []string{"testInput", "input"}
	for _, fileName := range filenames {
		fileContents, err := os.ReadFile(fileName)
		if err != nil {
			panic(err)
		}
		fmt.Println(fileName)
		lines := strings.Split(string(fileContents), "\n")
		chars = make([][]string, len(lines))
		for index, line := range lines {
			lineChars := strings.Split(line, "")
			chars[index] = lineChars
		}
		var xmases int
		for y := 0; y < len(chars); y++ {
			for x := 0; x < len(chars[y]); x++ {
				xmases += countXMasAtAPosition(x, y)
			}
		}
		println(xmases)
	}
}
func countXMasAtAPosition(x int, y int) int {
	if chars[y][x] != "A" {
		return 0
	}

	if x-1 < 0 || x+1 >= len(chars[y]) || y-1 < 0 || y+1 >= len(chars) {
		return 0
	}
	var xmases int
	//M.S
	//.A.
	//M.S
	if chars[y-1][x-1] == "M" && chars[y+1][x-1] == "M" && chars[y+1][x+1] == "S" && chars[y-1][x+1] == "S" {
		xmases++
	}
	//S.M
	//.A.
	//S.M
	if chars[y-1][x-1] == "S" && chars[y+1][x-1] == "S" && chars[y+1][x+1] == "M" && chars[y-1][x+1] == "M" {
		xmases++
	}

	//M.M
	//.A.
	//S.S
	if chars[y-1][x-1] == "M" && chars[y+1][x-1] == "S" && chars[y+1][x+1] == "S" && chars[y-1][x+1] == "M" {
		xmases++
	}

	//S.S
	//.A.
	//M.M
	if chars[y-1][x-1] == "S" && chars[y+1][x-1] == "M" && chars[y+1][x+1] == "M" && chars[y-1][x+1] == "S" {
		xmases++
	}
	return xmases
}
