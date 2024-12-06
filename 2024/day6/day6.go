package main

import (
	"fmt"
	"os"
	"strings"
)

type DirectionType int32

const (
	down  DirectionType = 1
	up    DirectionType = 2
	left  DirectionType = 3
	right DirectionType = 4
)

var labyrinthMap [][]string

func main() {
	filenames := []string{"testInput", "input"}
	for _, fileName := range filenames {
		fileContents, err := os.ReadFile(fileName)
		if err != nil {
			panic(err)
		}
		fmt.Println(fileName)
		lines := strings.Split(string(fileContents), "\n")
		labyrinthMap = make([][]string, len(lines))
		visitedMap := make([][]bool, len(lines))

		for index, line := range lines {
			labyrinthMap[index] = strings.Split(line, "")
			visitedMap[index] = make([]bool, len(line))
		}
		var guardX int
		var guardY int
		for y := 0; y < len(labyrinthMap); y++ {
			for x := 0; x < len(labyrinthMap[y]); x++ {
				if labyrinthMap[y][x] == "^" {
					guardX = x
					guardY = y
					break
				}
			}
		}
		visitedMap[guardY][guardX] = true
		var direction = up
		var exited bool

		for {
			guardX, guardY, direction, exited = determineNextGuardPosition(guardX, guardY, direction)
			if exited {
				break
			}
			visitedMap[guardY][guardX] = true
		}
		var total int
		for y := 0; y < len(visitedMap); y++ {
			print("\n")
			for x := 0; x < len(visitedMap[y]); x++ {
				if visitedMap[y][x] {
					print("X")
					total++
				} else {
					print(labyrinthMap[y][x])
				}
			}
		}
		print("\n")

		println(total)
	}
}

func determineNextGuardPosition(guardX int, guardY int, direction DirectionType) (int, int, DirectionType, bool) {
	var newX = guardX
	var newY = guardY
	switch direction {
	case down:
		newY++
		break
	case up:
		newY--
		break
	case left:
		newX--
		break
	case right:
		newX++
		break
	}
	if newY < 0 || newX < 0 || newY >= len(labyrinthMap) || newX >= len(labyrinthMap[newY]) {
		return newX, newY, direction, true
	}

	if labyrinthMap[newY][newX] == "." || labyrinthMap[newY][newX] == "^" {
		return newX, newY, direction, false
	}
	var newDirection DirectionType
	switch direction {
	case down:
		newDirection = left
		break
	case up:
		newDirection = right
		break
	case left:
		newDirection = up
		break
	case right:
		newDirection = down
		break
	}

	return guardX, guardY, newDirection, false
}
