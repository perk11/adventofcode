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
				xmases += countXMasAtMPosition(x, y)
			}
		}
		println(xmases)
	}
}
func countXMasAtMPosition(x int, y int) int {
	if chars[y][x] != "M" {
		return 0
	}
	var xmases int
	//XMAS
	if x-1 >= 0 && x+2 < len(chars[y]) {
		if chars[y][x-1] == "X" && chars[y][x+1] == "A" && chars[y][x+2] == "S" {
			xmases++
		}
	}
	//SAMX
	if x-2 >= 0 && x+1 < len(chars[y]) {
		if chars[y][x+1] == "X" && chars[y][x-1] == "A" && chars[y][x-2] == "S" {
			xmases++
		}
	}
	//X
	//M
	//A
	//S
	if y-1 >= 0 && y+2 < len(chars) {
		if chars[y-1][x] == "X" && chars[y+1][x] == "A" && chars[y+2][x] == "S" {
			xmases++
		}
	}
	//S
	//A
	//M
	//X
	if y-2 >= 0 && y+1 < len(chars) {
		if chars[y+1][x] == "X" && chars[y-1][x] == "A" && chars[y-2][x] == "S" {
			xmases++
		}
	}

	//X
	//  M
	//   A
	//     S
	if x-1 >= 0 && x+2 < len(chars[y]) && y-1 >= 0 && y+2 < len(chars) {
		if chars[y-1][x-1] == "X" && chars[y+1][x+1] == "A" && chars[y+2][x+2] == "S" {
			xmases++
		}
	}
	//S
	//  A
	//    M
	//      X
	if x-2 >= 0 && x+1 < len(chars[y]) && y-2 >= 0 && y+1 < len(chars) {
		if chars[y+1][x+1] == "X" && chars[y-1][x-1] == "A" && chars[y-2][x-2] == "S" {
			xmases++
		}
	}
	//   X
	//  M
	// A
	//S
	if y-1 >= 0 && y+2 < len(chars) && x-2 >= 0 && x+1 < len(chars[y]) {
		if chars[y-1][x+1] == "X" && chars[y+1][x-1] == "A" && chars[y+2][x-2] == "S" {
			xmases++
		}
	}

	//   S
	//  A
	// M
	//X
	if y+1 < len(chars) && y-2 >= 0 && x-1 >= 0 && x+2 < len(chars[y]) {
		if chars[y+1][x-1] == "X" && chars[y-1][x+1] == "A" && chars[y-2][x+2] == "S" {
			xmases++
		}
	}
	return xmases
}
